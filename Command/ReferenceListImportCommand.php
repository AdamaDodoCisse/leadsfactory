<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 27/10/15
 * Time: 16:55
 */

namespace Tellaw\LeadsFactoryBundle\Command;

use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Monolog\Logger;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Acl\Exception\Exception;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;

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
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $formatter = $this->getHelper('formatter');
        $logger = $this->getContainer()->get('export.logger');

        $helper = $this->getHelper('question');
        $question = new Question('Combien de listes devons nous impoter ? ', 1);
        $this->nbLists = $helper->ask($input, $output, $question);

        for ($i = 0; $i < $this->nbLists; $i++) {
            $question = new Question("Quel est le code de la liste " . ($i + 1) . "/" . $this->nbLists . " ? ");
            $response = trim($helper->ask($input, $output, $question));
            $this->listsOrder [$i] = $response;
            $this->lists[$response] = null;
        }

        // Load Lists
        foreach ($this->lists as $key => $list) {
            $this->lists[$key] = $this->getContainer()->get('leadsfactory.reference_list_repository')->findOneByCode($key);
            if ($this->lists[$key] == null) {
                throw new \Exception ("La liste n'a pas été trouvée : " . $key);
            }
        }

        // First Delete lists
        $itemsToDelete = array_reverse($this->lists);
        foreach ($itemsToDelete as $key => $list) {
            $this->deleteElementsForListId($list->getId());
        }

        // Reload elements
        $csvFile = $input->getArgument('csvFile');
        $csvContent = $this->readCsv($csvFile);

        // Import two level list
        $result = $this->loadTwoLevelList($csvContent);

        $this->processTwoLevelElements($result);

    }

    private function readCsv($csvFile)
    {

        if (!file_exists($csvFile)) {
            throw new \Exception ("File not found : " . $csvFile);
        }

        $csvContent = array();
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
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

                $elements[$item[0]] = array("name" => $item[1],
                    "childrens" => array());

            }

            $elements[$item[0]]["childrens"][] = array("name" => $item[3],
                "value" => $item[2]);

        }

        return $elements;

    }

    private function processTwoLevelElements($content)
    {

        $em = $this->getContainer()->get("doctrine")->getEntityManager();

        $list0 = $this->listsOrder[0];
        $list1 = $this->listsOrder[1];

        foreach ($content as $key => $item) {

            $element = new ReferenceListElement();
            $element->setReferenceList($this->lists[$list0]);
            $element->setName($item ["name"]);
            $element->setValue($key);
            $element->setStatus(1);
            $em->persist($element);

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
        $qb->delete("Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement", "e");
        $qb->where("e.referencelist_id = :id");
        $qb->setParameter("id", $listId);
        $qb->getQuery()->execute();

    }

}