<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Utils\ElasticSearchUtils;

class SearchIndexerCommand extends ContainerAwareCommand {
	
	private $cronjobs = array();
	private $dbConnection = null;

	private $nbOfItemsToBatch = 30000;

	private $leadsMaxId = null;

	protected function configure() {
		$this
		->setName('leadsfactory:searchIndexer')
		->setDescription('Indexer for Elastic Search');
	}

    protected function execute(InputInterface $input, OutputInterface $output) {

		// Get DB parameters. Not using Doctrine for better performances
		$dbUser = $this->getContainer()->getParameter('database_user');
		$dbPwd = $this->getContainer()->getParameter('database_password');
		$dbName = $this->getContainer()->getParameter('database_name');
		$dbHost = $this->getContainer()->getParameter('database_host');
		$dbPort = $this->getContainer()->getParameter('database_port');

		if (trim ($dbPort) == "")
			$dbPort = "3306";

		$this->dbConnection = new \mysqli( $dbHost, $dbUser, $dbPwd, $dbName);
		if (\mysqli_connect_error()) {
			die('Erreur de connexion (' . \mysqli_connect_errno() . ') '
				. \mysqli_connect_error());
		}

		mysqli_query($this->dbConnection, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

		// Iterate of scopes
		$sql = "SELECT id,name,code FROM Scope";
		$result = mysqli_query( $this->dbConnection , $sql);

		//$result = $mysqli->query( $sql );
		while($donnees = mysqli_fetch_object($result))
		{
			echo $donnees->id. " - ".$donnees->name. " - ".$donnees->code."\n";
			$this->exportScope( $donnees->code, $donnees->id );
		}

	}

	private function getFormsInScope ( $scopeId ) {

		$forms = "-1";

		$sql = "SELECT id FROM Form WHERE scope=".$scopeId;
		$result = mysqli_query( $this->dbConnection , $sql);
		while($obj = mysqli_fetch_object($result))
		{
			$forms .= ",".$obj->id;
		}

		return $forms;
	}

	private function getNbLeadsByScopeId ( $scopeId ) {

		$formsInScope = $this->getFormsInScope( $scopeId );

		// SQL Query to get the max Lead
		$sql = "SELECT count(id) as maxid FROM Leads WHERE form_id IN (".$formsInScope.")";
		$result = mysqli_query( $this->dbConnection , $sql);
		$obj = mysqli_fetch_object($result);

		return $obj->maxid;
	}

	private function exportScope ( $scopeCode, $scopeId ) {
		$this->exportLeads( $scopeCode, $scopeId );
	}

	private function exportLeads ( $scopeCode, $scopeId ) {

		$searchUtils = $this->getContainer()->get("search.utils");
		$countLeads = $this->getNbLeadsByScopeId( $scopeId );

		echo ("Leads dans le scope : ".$countLeads."\n\n");

		$leadRepository = $this->getContainer()->get('leadsfactory.leads_repository');

		for ($loopidx = 0; $loopidx <= $countLeads; $loopidx=$loopidx+$this->nbOfItemsToBatch ) {


			//$sql = "SELECT * FROM Leads WHERE form_id IN (".$this->getFormsInScope($scopeId).") LIMIT ".$loopidx.",".$this->nbOfItemsToBatch;

			$sql = "
				SELECT 	L.id, L.email, L.firstname, L.lastname, DATE_FORMAT(L.createdAt, '%Y-%m-%dT%TZ') as createdAt, content, DATE_FORMAT(L.exportdate, '%Y-%m-%dT%TZ') as exportDate, 
						L.ipadress as ipaddress, L.userAgent, L.utmcampaign, L.workflowStatus, L.workflowTheme, L.workflowType, L.user,
						F.id as formId, F.name as formName, F.code as formCode,
						U.id as userId, U.lastname as userLastName, U.firstname as userFirstName, U.email as userEmail,
						S.id as scopeId, S.name as scopeName, S.code as scopeCode,
						FT.id as formTypeId, FT.name as formTypeName
				
				FROM `Leads` as L
				
				LEFT JOIN `Users` as U on L.user = U.id
				LEFT JOIN `Form` as F on L.form_id = F.id
				LEFT JOIN `Scope` as S on F.scope = S.id
				LEFT JOIN `FormType` as FT on F.type_id = FT.id

				WHERE L.form_id IN (".$this->getFormsInScope($scopeId).") LIMIT ".$loopidx.",".$this->nbOfItemsToBatch;

			//echo $sql."\n";
			$result = mysqli_query( $this->dbConnection , $sql);

			$leadStream = "";

			while($obj = mysqli_fetch_assoc($result))
			{

				$obj["content"] = json_decode( $obj["content"] );

				$tmpLeadStream = json_encode(  $obj  );

				if (trim($tmpLeadStream) != "") {
					$leadStream .= "{ \"index\" : { \"_index\" : \"leadsfactory-".$scopeCode."\", \"_type\" : \"leads\", \"_id\" : \"".$obj["id"]."\" } }\n";
					$leadStream .= $tmpLeadStream."\n";
				}

				unset( $tmpLeadStream );
			}

			// Send Stream to search engine
			$response = $searchUtils->request( ElasticSearchUtils::$PROTOCOL_POST, "/leadsfactory-".$scopeId."/leads/_bulk", $leadStream, false );

			unset ($leadStream);

			unset ($obj);
			unset ($result);
			unset ($sql);




		}

	}


}