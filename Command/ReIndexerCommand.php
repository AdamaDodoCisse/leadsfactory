<?php


namespace Tellaw\LeadsFactoryBundle\Command;


use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\Scope;
use Tellaw\LeadsFactoryBundle\Utils\ElasticSearchUtils;

class ReIndexerCommand extends ContainerAwareCommand
{

    const MAX_BATCH = 1000;

    /**
     * Command configuration
     */
    protected function configure()
    {
        $this->setName('leadsfactory:searchIndexer:reload')
            ->setDescription('Deletes given index and reload from database')
            ->addArgument('scope', InputArgument::OPTIONAL, 'Scope of the index', 'all');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /**
         * Get the called scope
         */
        $scopeName = $input->getArgument('scope');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var EntityRepository
         */
        $scopeRepository = $em->getRepository('TellawLeadsFactoryBundle:Scope');
        if ($scopeName == 'all') {
            $scopes = $scopeRepository->findAll();
        } else {
            $scopes = $scopeRepository->findByCode($scopeName);
        }

        foreach ($scopes as $scope) {
            $output->writeln("## RE INDEXATION DES DONNEES DE ".$scope->getCode());
            $this->exportLeads($scope, $output);
        }


        return null;
    }

    /**
     * @param Scope $scope
     * @param OutputInterface $output
     * @internal param $scopeCode
     * @internal param $scopeId
     */
    private function exportLeads(Scope $scope, OutputInterface $output)
    {

        $indexName = "leadsfactory-".$scope->getCode();
        $output->writeln("Récupération des Formulaires ...");
        /**
         * ElasticSearch Tool
         */
        $ElasticSearchUtils = $this->getContainer()->get("search.utils");
        $formsInScope = $this->getFormsIdsInScope($scope);
        $output->writeln(count($formsInScope)." Formulaires à traiter pour le scope.");

        $output->writeln("Récupération des Leads ...");
        /**
         * Recuperation des leads
         */
        $leads = $this->getLeads($formsInScope);
        $countLeads = count($leads);
        $output->writeln($countLeads." Leads à traiter pour le scope.");

        if ($countLeads) {
            $output->writeln("Suppression de l'index Elasticsearch : ".$indexName);
            $ElasticSearchUtils->request(
                ElasticSearchUtils::$PROTOCOL_DELETE,
                "/".$indexName."/",
                null,
                false
            );

        } else {
            $output->writeln("Fin du traitement");

            return;
        }


        /**
         * Traitement des leads par ventilation
         */
        $output->writeln("Traitement des Leads avec une ventillation de ".self::MAX_BATCH."...");
        $leadStream = "";
        foreach ($leads as $i => $lead) {
            $lead["content"] = json_decode($lead["content"]);
            $jsonLeadStream = json_encode($lead);

            // Recuperation du contenu
            if (trim($jsonLeadStream) != "") {
                $leadStream .= "{ \"index\" : { \"_index\" : \"".
                    $indexName."\", \"_type\" : \"leads\", \"_id\" : \"".
                    $lead["id"]."\" } }\n";
                $leadStream .= $jsonLeadStream."\n";
            }

            // si on a assez d'elements dans la liste on envoie
            // Si on est a la fin du tableau d'elements on envoie
            $iPos = $i + 1;
            if ($iPos != 0 && ($iPos % self::MAX_BATCH == 0 || $iPos == $countLeads)) {
                $prct = round(($iPos / $countLeads) * 100);
                $response = $ElasticSearchUtils->request(
                    ElasticSearchUtils::$PROTOCOL_POST,
                    "/".$indexName."/leads/_bulk",
                    $leadStream,
                    false
                );
                $output->write("\rIndexation : ".$prct."% - (".$iPos." sur ".$countLeads.")");

                // On Vide le feed
                $leadStream = "";
            }

        }

    }

    /**
     * Recuperation de la liste d'Ids de formulaires correspondant au Scope fourni
     * @param Scope $scope
     * @return array|string
     */
    private function getFormsIdsInScope(Scope $scope)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $formsRepository = $em->getRepository("TellawLeadsFactoryBundle:Form");
        $forms = $formsRepository->findByScope($scope);
        $formIds = array();
        foreach ($forms as $form) {
            $formIds[] = $form->getId();
        }

        return $formIds;
    }

    /**
     * Recuperation de la list de leads correspondant à la list d'Ids de formulaire fournie
     * @param $formsInScope
     */
    private function getLeads($formsInScope)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $dql = "
				SELECT 	L.id, L.email, L.firstname, L.lastname, DATE_FORMAT(L.createdAt, '%Y-%m-%dT%TZ') as createdAt, L.data as content, DATE_FORMAT(L.exportdate, '%Y-%m-%dT%TZ') as exportDate,
						L.ipadress as ipaddress, L.userAgent, L.utmcampaign, L.workflowStatus, L.workflowTheme, L.workflowType, U.id as user,
						F.id as formId, F.name as formName, F.code as formCode,
						U.id as userId, U.lastname as userLastName, U.firstname as userFirstName, U.email as userEmail,
						S.id as scopeId, S.name as scopeName, S.code as scopeCode,
						FT.id as formTypeId, FT.name as formTypeName

				FROM TellawLeadsFactoryBundle:Leads as L

				LEFT JOIN L.user U
				LEFT JOIN L.form F
				LEFT JOIN F.scope S
				LEFT JOIN F.formType FT

				WHERE F.id IN ( :formsinScope )";

        $query = $em->createQuery($dql);
        $query->setParameter("formsinScope", $formsInScope);
//        $query->setMaxResults(100);
        $leads = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return $leads;
    }
}
