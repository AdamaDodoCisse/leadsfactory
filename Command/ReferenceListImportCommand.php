<?php
namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Symfony\Component\Console\Input\InputOption;

class ReferenceListImportCommand extends ContainerAwareCommand
{

    private $nbLists = null;
    private $lists = array();
    private $listsOrder = array();

    protected function configure()
    {

        $this
            ->setName('leadsfactory:referenceList:import')
            ->setDescription('Reference list import')
            ->addArgument(
                'csvFile',
                InputArgument::OPTIONAL,
                'nom du fichier CSV à importer'
            )
        ->addOption('delimiter', 'd', InputOption::VALUE_OPTIONAL, 'delimiter', ';')
        ->addOption('truncate', 'tr', InputOption::VALUE_OPTIONAL, 'delete if exist ?', true);

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $formatter = $this->getHelper('formatter');
        $logger = $this->getContainer()->get('export.logger');

        $helper = $this->getHelper('question');
        $question = new Question('Combien de listes devons nous impoter ? ', 1);
        $this->nbLists = $helper->ask($input, $output, $question);

        for ($i = 0; $i < $this->nbLists; $i++) {
            $question = new Question("Quel est le code de la liste ".($i + 1)."/".$this->nbLists." ? ");
            $response = trim($helper->ask($input, $output, $question));
            $this->listsOrder [$i] = $response;
            $this->lists[$response] = null;
        }

        // Load Lists
        foreach ($this->lists as $key => $list) {
            $this->lists[$key] = $this->getContainer()->get('leadsfactory.reference_list_repository')->findOneByCode(
                $key
            );
            if ($this->lists[$key] == null) {
                throw new \Exception ("La liste n'a pas été trouvée : ".$key);
            }
        }


        if ($input->getOption('truncate')) {
            // First Delete lists
            $itemsToDelete = array_reverse($this->lists);
            foreach ($itemsToDelete as $key => $list) {
                $this->deleteElementsForListId($list->getId());
            }
        }

        // Reload elements
        $csvFile = $input->getArgument('csvFile');
        $delimiter = $input->getOption('delimiter');
        $csvContent = $this->readCsv($csvFile, $delimiter);

        // Import two level list
        $result = $this->loadTwoLevelList($csvContent);

        $this->processTwoLevelElements($result);

    }

    private function readCsv($csvFile, $delimiter)
    {

        if (!file_exists($csvFile)) {
            throw new \Exception ("File not found : ".$csvFile);
        }

        $csvContent = array();
        if (($handle = fopen($csvFile, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, "$delimiter")) !== false) {
                $csvContent[] = $data;
            }
            fclose($handle);
        }

        return $csvContent;
    }

    private function loadTwoLevelList($content)
    {

        $elements = array();

        foreach ($content as $item) {

            if (!array_key_exists($item[0], $elements)) {

                $elements[$item[0]] = array("name" => $item[1], "childrens" => array());

            }

            if (count($item) > 2) {
                $elements[$item[0]]["childrens"][] = array("name" => $item[3], "value" => $item[2]);
            }

        }
        return $elements;
    }

    private function processTwoLevelElements($content)
    {

        $em = $this->getContainer()->get("doctrine")->getEntityManager();

        $list0 = $this->listsOrder[0];
        if (count($this->listsOrder) == 2) {
            $list1 = $this->listsOrder[1];
        }
        foreach ($content as $key => $item) {

            $element = new ReferenceListElement();
            $element->setReferenceList($this->lists[$list0]);
            $element->setName($item ["name"]);
            $element->setValue($key);
            $element->setStatus(1);
            $em->persist($element);
            if (!isset($list1)) {
                continue;
            }

            foreach ($item["childrens"] as $children) {

                $childElement = new ReferenceListElement();
                $childElement->setReferenceList($this->lists[$list1]);
                $childElement->setParent($element);
                $childElement->setName($children["name"]);
                $childElement->setValue($children["value"]);
                $childElement->setStatus(1);
                $em->persist($childElement);

            }

        }

        echo "Fin de l'importation\n";
        $em->flush();

    }

    /**
     *
     * Method used to delete every elements of a list in the DB
     *
     * @param $listId integer List Id
     */
    private function deleteElementsForListId($listId)
    {

        $em = $this->getContainer()->get("doctrine")->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->delete("Tellaw\\LeadsFactoryBundle\\Entity\\ReferenceListElement", "e");
        $qb->where("e.referencelist_id = :id");
        $qb->setParameter("id", $listId);
        $qb->getQuery()->execute();

    }

}
