<?php

namespace Weka\LeadsExportBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Weka\LeadsExportBundle\Utils\AthenaV2;

class AthenaV2Test extends WebTestCase
{

	private $container = null;

	private $_DATASET_ETUDIANT_1	= ["profil" => "ETUDIANT"];
	private $_DATASET_ETUDIANT_2 	= ["profil" => "etudiant"];
	private $_DATASET_ETUDIANT_3 	= ["profil" => "Etudiant"];
	private $_DATASET_PRO_1		 	= ["profil" => "PROFESSIONNEL"];

	//private $_FORM_DATASET_CONFIG ['export']['athenaV2']['mapping_class'] = "test"

	private $_DATASET_ACTEUR_FORCE_TMK = ["nom" => "test", "acteur" => "tmk", "profil" => "PROFESSIONNEL"];
	private $_DATASET_NEUTRE = ["nom" => "test", "profil" => "PROFESSIONNEL"];

	private $_DATASET_PRO = "

	";

	public function setUp()
	{
		self::bootKernel();
		$this->container = self::$kernel->getContainer();
	}

	/**
	 * Ce test va vérifier que la DI n'est pas exportable  car ETUDIANT
	 */
	public function testExportEtudiant_1_IsExportable () {

		$job = new Export();
		$form = new Form();
		$lead = new Leads();
		$job->setLead( $lead );

		$baseMapping = $this->getMockBuilder('AthenaV2BaseMapping')->getMock();

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->_mappingClass = $baseMapping;
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_ETUDIANT_1 );

		$this->assertEquals(false, $isExportable);

	}

	/**
	 * Ce test va vérifier que la DI n'est pas exportable  car ETUDIANT
	 */
	public function testExportEtudiant_2_IsExportable () {

		$job = new Export();
		$form = new Form();
		$lead = new Leads();
		$job->setLead( $lead );

		$baseMapping = $this->getMockBuilder('AthenaV2BaseMapping')->getMock();

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->_mappingClass = $baseMapping;
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_ETUDIANT_2 );

		$this->assertEquals(false, $isExportable);

	}

	/**
	 * Ce test va vérifier que la DI n'est pas exportable  car ETUDIANT
	 */
	public function testExportEtudiant_3_IsExportable () {

		$job = new Export();
		$form = new Form();
		$lead = new Leads();
		$job->setLead( $lead );

		$baseMapping = $this->getMockBuilder('AthenaV2BaseMapping')->getMock();

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->_mappingClass = $baseMapping;
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_ETUDIANT_3 );

		$this->assertEquals(false, $isExportable);

	}

	/**
	 * Ce test va vérifier que la DI est exportable  car PRO
	 */
	public function testExportPro_1_IsExportable () {

		$job = new Export();
		$form = new Form();
		$lead = new Leads();
		$job->setLead( $lead );

		$baseMapping = $this->getMockBuilder('AthenaV2BaseMapping')->getMock();

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->_mappingClass = $baseMapping;
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_PRO_1 );

		$this->assertEquals(true, $isExportable);

	}

	public function testAssignationParDonnees () {

		$job = new Export();
		$form = new Form();
		$lead = new Leads();
		$job->setLead( $lead );

		// Create instance of base mapping
		$em = $this->container->get('doctrine')->getManager();
		$list_element_repository = $this->container->get('leadsfactory.reference_list_element_repository');
		$className = "\\Weka\\LeadsExportBundle\\Utils\\AthenaV2\\ti\\Classic";
		$instance = new $className($em, $list_element_repository);

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->_mappingClass = $instance;
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_ACTEUR_FORCE_TMK );
		$this->assertEquals(true, $isExportable);

		$requestData = $export->updateDrcData("123", $this->_DATASET_ACTEUR_FORCE_TMK, "456", null, "789", "159", "testleads");

		$this->assertEquals("tmk", $requestData->acteur);
	}

	public function testAssignationParFormulaireFausse () {

		$job = new Export();
		$form = new Form();
		$formConfig = "
{
  \"export\": {
    \"athenaV2\": {
      \"acteur\": \"TmK\"
    }
  }
}
		";
		//$formConfig = array ( "export" => array ( "athenaV2" => array ( "acteur" => "tmk" ) ) );
		$form->setExportConfig( $formConfig );

		$lead = new Leads();
		$job->setLead( $lead );

		// Create instance of base mapping
		$em = $this->container->get('doctrine')->getManager();
		$list_element_repository = $this->container->get('leadsfactory.reference_list_element_repository');
		$className = "\\Weka\\LeadsExportBundle\\Utils\\AthenaV2\\ti\\Classic";
		$instance = new $className($em, $list_element_repository);

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->_mappingClass = $instance;
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_NEUTRE );
		$this->assertEquals(true, $isExportable);

		$data = $export->preProcessData( $this->_DATASET_NEUTRE );
		$requestData = $export->updateDrcData("123", $data, "456", null, "789", "159", "testleads");

		$this->assertNotEquals("tmk", $requestData->acteur);
	}

	public function testAssignationParFormulaire () {

		$job = new Export();
		$form = new Form();
		$formConfig = "
{
  \"export\": {
    \"athenaV2\": {
      \"acteur\": \"tmk\"
    }
  }
}
		";
		//$formConfig = array ( "export" => array ( "athenaV2" => array ( "acteur" => "tmk" ) ) );
		$form->setExportConfig( $formConfig );

		$lead = new Leads();
		$job->setLead( $lead );

		// Create instance of base mapping
		$em = $this->container->get('doctrine')->getManager();
		$list_element_repository = $this->container->get('leadsfactory.reference_list_element_repository');
		$className = "\\Weka\\LeadsExportBundle\\Utils\\AthenaV2\\ti\\Classic";
		$instance = new $className($em, $list_element_repository);

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->_mappingClass = $instance;
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_NEUTRE );
		$this->assertEquals(true, $isExportable);

		$data = $export->preProcessData( $this->_DATASET_NEUTRE );
		$requestData = $export->updateDrcData("123", $data, "456", null, "789", "159", "testleads");

		$this->assertEquals("tmk", (string)$requestData->acteur);
	}

	public function isDrc () {
		$job = new Export();
		$form = new Form();
		$formConfig = "
			{
			  \"export\": {
				\"athenaV2\": {
				  \"acteur\": \"tmk\",
				  \"method\": \"drc\",
				}
			  }
			}
		";
		//$formConfig = array ( "export" => array ( "athenaV2" => array ( "acteur" => "tmk" ) ) );
		$form->setExportConfig( $formConfig );

		$lead = new Leads();
		$job->setLead( $lead );

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_NEUTRE );
		$this->assertEquals(true, $isExportable);

		$isDrc = $export->isDrc( $this->_DATASET_NEUTRE );

		$this->assertEquals(true, $isDrc);
	}

	public function isNotDrc () {
		$job = new Export();
		$form = new Form();
		$formConfig = "
			{
			  \"export\": {
				\"athenaV2\": {
				  \"acteur\": \"tmk\",
				  \"method\": \"affaire\",
				}
			  }
			}
		";
		//$formConfig = array ( "export" => array ( "athenaV2" => array ( "acteur" => "tmk" ) ) );
		$form->setExportConfig( $formConfig );

		$lead = new Leads();
		$job->setLead( $lead );

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_NEUTRE );
		$this->assertEquals(true, $isExportable);

		$isDrc = $export->isDrc( $this->_DATASET_NEUTRE );

		$this->assertEquals(false, $isDrc);
	}

	public function isAffaire () {
		$job = new Export();
		$form = new Form();
		$formConfig = "
			{
			  \"export\": {
				\"athenaV2\": {
				  \"acteur\": \"tmk\",
				  \"method\": \"affaire\",
				}
			  }
			}
		";
		//$formConfig = array ( "export" => array ( "athenaV2" => array ( "acteur" => "tmk" ) ) );
		$form->setExportConfig( $formConfig );

		$lead = new Leads();
		$job->setLead( $lead );

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_NEUTRE );
		$this->assertEquals(true, $isExportable);

		$isDrc = $export->isDrc( $this->_DATASET_NEUTRE );

		$this->assertEquals(true, $isDrc);
	}

	public function isNotAffaire () {
		$job = new Export();
		$form = new Form();
		$formConfig = "
			{
			  \"export\": {
				\"athenaV2\": {
				  \"acteur\": \"tmk\",
				  \"method\": \"drc\",
				}
			  }
			}
		";
		//$formConfig = array ( "export" => array ( "athenaV2" => array ( "acteur" => "tmk" ) ) );
		$form->setExportConfig( $formConfig );

		$lead = new Leads();
		$job->setLead( $lead );

		$export = $this->container->get("athenav2_method");
		$export->init( $form );
		$export->isTestMode = true;

		$isExportable = $export->isExportable ( $job, $form, $this->_DATASET_NEUTRE );
		$this->assertEquals(true, $isExportable);

		$isDrc = $export->isDrc( $this->_DATASET_NEUTRE );

		$this->assertEquals(false, $isDrc);
	}

}