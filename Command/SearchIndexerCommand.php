<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchIndexerCommand extends ContainerAwareCommand {
	
	private $cronjobs = array();
	private $dbConnection = null;

	private $nbOfItemsToBatch = 1000;

	private $leadsMaxId = null;

	protected function configure() {
		$this
		->setName('leadsfactory:searchIndexer')
		->setDescription('Indexer for Elastic Search')
		//->addArgument('objectTpye', InputArgument::OPTIONAL, 'Specify the type of object to index')
		;
	}

    protected function execute(InputInterface $input, OutputInterface $output) {

		//$objectType = trim($input->getArgument('objectType'));


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
		$sql = "SELECT id,name FROM Scope";
		$result = mysqli_query( $this->dbConnection , $sql);

		//$result = $mysqli->query( $sql );
		while($donnees = mysqli_fetch_object($result))
		{
			echo $donnees->id. " - ".$donnees->name."\n";
			$this->exportScope( $donnees->id );
		}


		// SQL Query to get the max Lead


		//Loop for lead indexation


		// SQL Query to get the max export

		// Loop for the export indexation


		// SQL Query to get the max form id

		// Loop for Form indexation

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

	private function exportScope ( $scopeId ) {
		$this->exportLeads( $scopeId );
	}

	private function exportLeads ( $scopeId ) {

		$searchUtils = $this->getContainer()->get("search.utils");

		$countLeads = $this->getNbLeadsByScopeId( $scopeId );

		$idxElementNum = 1;

		for ($loopidx = 0; $loopidx <= $countLeads; $loopidx=$loopidx+$this->nbOfItemsToBatch ) {

			echo ("Index : ".$loopidx);
			//$maxValue = $loopidx+$this->nbOfItemsToBatch-1;

			$sql = "SELECT * FROM Leads WHERE form_id IN (".$this->getFormsInScope($scopeId).") LIMIT ".$loopidx.",".$this->nbOfItemsToBatch;
			echo "\n".$sql."\n";

			$result = mysqli_query( $this->dbConnection , $sql);
			while($obj = mysqli_fetch_assoc($result))
			{
				echo $idxElementNum++."/".$countLeads." -> ".$obj["id"]. " - ".$obj["email"]."\n";

				// Send to Search Engine
				$searchUtils->indexLeadObject( $obj, $scopeId );

			}
			unset ($result);
			unset ($sql);
		}
		//Loop for lead indexation



	}


}