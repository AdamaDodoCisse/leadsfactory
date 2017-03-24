<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Utils\ElasticSearchUtils;

class SearchIndexerCommand extends ContainerAwareCommand
{

    private $cronjobs = array();
    private $dbConnection = null;

    private $nbOfItemsToBatch = 30000;

    private $leadsMaxId = null;

    protected function configure()
    {
        $this
            ->setName('leadsfactory:searchIndexer')
            ->setDescription('Indexer for Elastic Search');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("SELECT s.id, s.name, s.code FROM TellawLeadsFactoryBundle:Scope s");
        $results = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        foreach ($results as $donnees) {
            echo $donnees["id"] . " - " . $donnees["name"] . " - " . $donnees["code"] . "\n";
            $this->exportScope($donnees["code"], $donnees["id"]);
        }

    }

    private function getFormsInScope($scopeId)
    {

        $forms = "-1";

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("SELECT f.id FROM TellawLeadsFactoryBundle:Form f WHERE f.scope=:scope");
        $query->setParameter("scope", $scopeId);
        $results = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $forms = array();

        foreach ($results as $obj) {
            $forms [] = $obj["id"];
        }

        return $forms;
    }

    private function getNbLeadsByScopeId($scopeId)
    {

        $formsInScope = $this->getFormsInScope($scopeId);

        // SQL Query to get the max Lead
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("SELECT count(l.id) as nbleads FROM TellawLeadsFactoryBundle:Leads l WHERE l.form IN ( :forms )");
        $query->setParameter("forms", $formsInScope);

        $results = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return $results[0]["nbleads"];
    }

    private function exportScope($scopeCode, $scopeId)
    {
        $this->exportLeads($scopeCode, $scopeId);
    }

    private function exportLeads($scopeCode, $scopeId)
    {

        $searchUtils = $this->getContainer()->get("search.utils");
        $countLeads = $this->getNbLeadsByScopeId($scopeId);

        echo("Leads dans le scope : " . $countLeads . "\n\n");

        $leadRepository = $this->getContainer()->get('leadsfactory.leads_repository');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $formsInScope = $this->getFormsInScope($scopeId);

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

        for ($loopidx = 0; $loopidx <= $countLeads; $loopidx = $loopidx + $this->nbOfItemsToBatch) {

            $query->setParameter("formsinScope", $formsInScope);
            $query->setFirstResult($loopidx);
            $query->setMaxResults($this->nbOfItemsToBatch);
            $results = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

            $leadStream = "";

            foreach ($results as $obj) {

                $obj["content"] = json_decode($obj["content"]);

                $tmpLeadStream = json_encode($obj);

                if (trim($tmpLeadStream) != "") {
                    $leadStream .= "{ \"index\" : { \"_index\" : \"leadsfactory-" . $scopeCode . "\", \"_type\" : \"leads\", \"_id\" : \"" . $obj["id"] . "\" } }\n";
                    $leadStream .= $tmpLeadStream . "\n";
                }

                unset($tmpLeadStream);
            }

            // Send Stream to search engine
            $response = $searchUtils->request(ElasticSearchUtils::$PROTOCOL_POST, "/leadsfactory-" . $scopeId . "/leads/_bulk", $leadStream, false);

            unset ($leadStream);

            unset ($obj);
            unset ($result);
            unset ($sql);

        }

    }

}
