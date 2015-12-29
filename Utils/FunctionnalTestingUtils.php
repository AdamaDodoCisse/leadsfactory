<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\KibanaSearch;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Tellaw\LeadsFactoryBundle\Entity\SearchResult;
use Tellaw\LeadsFactoryBundle\Entity\UserPreferences;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FunctionnalTestingUtils implements ContainerAwareInterface {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    private $logger = null;
    private $outputInterface = null;

    private $isWebMode = false;

    // Field set array that saves values of test for the form, in order to validate values in the DB
    private $fieldSet = array();

    public static $_STATUS_NOT_TESTED = 0;
    public static $_STATUS_SUCCESS = 1;
    public static $_STATUS_FAILED = 2;

    public static $_VALIDATION_NO_MATCH = 0;
    public static $_VALIDATION_PARTIAL_MATCH = 1;
    public static $_VALIDATION_MATCH = 2;

    public static $_STEP_1_CREATE_CASPER_SCRIPT = 1;
    public static $_STEP_2_EXECUTE_CASPER_SCRIPT = 2;
    public static $_STEP_3_EVALUATE_LEADS = 3;
    public static $_STEP_4_PERSIST_RESULTS = 4;

    private $logContent = "";

    public function __construct (  ) {

        PreferencesUtils::registerKey( "CORE_LEADSFACTORY_URL",
                                        "Url de l'application, sur le scope global pour le BO, et sur les scopes pour les formulaires",
                                        PreferencesUtils::$_PRIORITY_OPTIONNAL
            );

        PreferencesUtils::registerKey( "CORE_CASPER_PATH",
                                        "Path to Casper install for functionnal testings.",
                                        PreferencesUtils::$_PRIORITY_OPTIONNAL);

    }

    /**
     * Method used to detect if recorded lead has been created by a test or not
     * @param Leads $lead
     * @return bool
     */
    public function isTestLead ( Leads $lead ) {
        if (strstr ($lead->getUserAgent(), 'casperjs')) {
            return true;
        }
        return false;
    }

    public function setIsWebMode ( $mode ) {
        $this->isWebMode = $mode;
    }

    public function setLogger ( $logger ) {
        $this->logger = $logger;
    }

    public function setOutputInterface ( $outputInterface ) {
        $this->outputInterface = $outputInterface;
    }

    public function isFormTestable ( Form $form ) {
        $formConfig = $form->getConfig();
        if (isset( $formConfig ["configuration"]["functionnalTestingEnabled"] ) && $formConfig ["configuration"]["functionnalTestingEnabled"] == true) {
            return true;
        } else {
            return false;
        }
    }

    public function log ( $msg ) {

        if ($this->logger != null) {
            $this->logger->info ( $msg );
            $this->logContent .= $msg."<br/>";
            echo ($msg."<br/>");
            \flush();
        }
        if ($this->outputInterface != null) {
            $this->outputInterface->writeln ($msg);
        }

    }

    public function runByStep ( $step, Form $form, $status = 0, $log = "", $resultOfTheTest = "" ) {

        switch ($step) {
            case FunctionnalTestingUtils::$_STEP_1_CREATE_CASPER_SCRIPT:

                // 1/ Check or create Jasper file for testing
                $testContent = $this->createJasperScript( $form );
                return $this->saveTest( $form, $testContent );

                break;

            case FunctionnalTestingUtils::$_STEP_2_EXECUTE_CASPER_SCRIPT:
                // 2/ Run Casper test
                if ($this->isCasperScriptExist( $form )) {

                    list ($status, $log) = $this->executeCasperTest( $form );
                    return array ($status, $log);

                } else {
                    throw new \Exception ("Issue while creating CASPER Script");
                }
                break;

            case FunctionnalTestingUtils::$_STEP_3_EVALUATE_LEADS:

                // 3/ Find in leads the test result
                if ($status) {
                    $leads = $this->findLeadsInDatabase( $form );
                    $resultOfTheTest = $this->validateTestResults( $this->fieldSet, $leads );
                    return $resultOfTheTest;
                }
                break;

            case FunctionnalTestingUtils::$_STEP_4_PERSIST_RESULTS:

                // 4/ Save status of test
                $form->setTestStatus( $resultOfTheTest );
                $form->setTestLog( $this->logContent );

                $this->log ("-- Saving test result");

                $em = $this->container->get("doctrine")->getManager();
                $em->persist($form);
                $em->flush();
                break;

        }

    }

    public function run (Form $form) {

        // Step 1
        $status = $this->runByStep( FunctionnalTestingUtils::$_STEP_1_CREATE_CASPER_SCRIPT, $form );

        if ( !$status ) {
            throw new \Exception ("Unable to create casper script");
        }

        // Step 2
        list ( $status, $log ) = $this->runByStep( FunctionnalTestingUtils::$_STEP_2_EXECUTE_CASPER_SCRIPT, $form );

        // Step 3
        $statusOfTest = $this->runByStep( FunctionnalTestingUtils::$_STEP_3_EVALUATE_LEADS, $form, $status, $log );

        // Step 4
        $this->runByStep( FunctionnalTestingUtils::$_STEP_4_PERSIST_RESULTS, $form, $status, $log, $statusOfTest );

    }

    /**
     *
     * Method used to get the path and name of the screenshot file
     * This is for the screenshot of the form
     *
     * @param  Form Object targeted by the screenshot
     * @return [String] path and filename
     */
    public function getScreenPathOfForm ( Form $form ) {


        // ex : /var/www/weka-leadsfactory/app
        $base = $this->container->get('kernel')->getRootDir();


        $screenshotDir = $base."/cache/screenshots";

        if (!is_dir( $screenshotDir )) {
            \mkdir ( $screenshotDir );
        }

        return $base."/cache/screenshots/form-".$form->getId().".jpg";

    }

    /**
     *
     * Method used to get the path and name of the screenshot file
     * This is for the screenshot of the result
     *
     * @param  Form Object targeted by the screenshot
     * @return [String] path and filename
     */
    public function getScreenPathOfResult ( Form $form ) {

        // ex : /var/www/weka-leadsfactory/app
        $base = $this->container->get('kernel')->getRootDir();

        $screenshotDir = $base."/cache/screenshots";

        if (!is_dir( $screenshotDir )) {
            \mkdir ( $screenshotDir );
        }

        return $base."/cache/screenshots/result-".$form->getId().".jpg";

    }

    /**
     *
     * Used to inject Symfony container to application
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function setContainer (ContainerInterface $container = null) {
        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    public function testForm ( $client, $form, $fields ) {

        $this->createJasperScript ( $form );

    }

    private function getCasperScriptPath ( Form $form ) {

        // ex : /var/www/weka-leadsfactory/app
        $base = $this->container->get('kernel')->getRootDir();

        return $base."/cache/casperjs/" . $form->getId() . "-test.js";
    }

    /**
     *
     * Checks if the casper script test file exists
     *
     * @param  Form Object to test with Casper
     * @return boolean
     */
    public function isCasperScriptExist ( Form $form ) {

        if ( file_exists( $this->getCasperScriptPath( $form ) )) {
            return true;
        }
        return false;
    }

    /**
     *
     * Method used to generate JasperJS Script for Testing
     *
     * @param $form
     * @return string
     */
    public function createJasperScript ( $form ) {

        $formUtils = $this->container->get("form_utils");
        $fields = array();

        $formId = $form->getConfig();
        if (isset( $formId ["configuration"]["formId"] )) {
            $formId = $formId ["configuration"]["formId"];
        } else {
            $formId = "leadsfactory-form";
        }

        // Init variables
        $sequences = array();
        $item = "";

        // Init values of test
        $frontUrl = $form->getUrl();

        if ( trim($frontUrl) == "" ) {

            // build preview URL
            $prefUtils = $this->container->get('preferences_utils');
            $leadsUrl = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', "");

            if ( trim($leadsUrl) == "" ) {
                throw new \Exception ("Lead's Factory URL not set in preference : CORE_LEADSFACTORY_URL");
            }

            $frontUrl = $leadsUrl."web/app_dev.php/client/preview/twig/".$form->getCode();

            $this->log ("Using preview url : ".$frontUrl);

        } else{
            $this->log ("Using declared url : ".$frontUrl);
        }

        // 2/ Lecture des champs
        $fields = $formUtils->getFieldsAsArray ( $form->getSource() );

        // Build Ordered sequences for testing
        $submit = "false";

        // Get the correct sequence of fields to test
        $sequencesToTest = $this->getSequencesToTest( $fields );

        // Get a screenshot of the form
        $this->getScreenShot( "formscreen" );

        // Render Sequences
        $nbSequences = count ($sequencesToTest);
        $sequenceIdx = 1;
        foreach ( $sequencesToTest as $idx => $sequence ) {

            if ($sequenceIdx == count ($sequencesToTest)) {
                $submit = "true";
            }
            $sequenceIdx++;

            $item .= $this->getScreenShot( "statusscreen" );

            $item .= "
                casper.then(function() {";

            // Adding delay to sequence if needed
            if (isset($sequence["delay"])) {
                $item .= "
                this.wait(".trim($sequence["delay"]).", function() {
            ";
            }

            $item .= "
                    this.echo (\"Sequence ".($idx+1)."/".$nbSequences."\");
                    this.fill(	'form[id=\"".$formId."\"]',
                        {
                            ";


            //
            // Loop over fields to add test values to the casperjs file
            // It also saves fields and value to this class for test validation
            //
            $fieldIdx = 0;
            foreach ( $sequence['fields'] as $field ) {

                // Find value for field
                $fieldValue = $this->getValueForField ( $field );

                // Create field
                if ( isset( $field["attributes"]["test-alias"] ) ) {
                    $fieldName = $field["attributes"]["test-alias"];
                } else {
                    $fieldName = $field["attributes"]["id"];
                }
                $item .= "'lffield[".$fieldName."]': '".$fieldValue."'";

                // Saves to this object value for later verification
                $this->saveFieldValue( $fieldName, $fieldValue );

                if ($fieldIdx != count ($sequence['fields'])-1) {
                    $item.= ",\r";
                }
                $fieldIdx++;
            }

            $item.= "
                        },
                    ".$submit.");";

            // Closing for delay desction
            if (isset($sequence["delay"])) {
                $item .= "});";
            }

            $item.= "
                });
            ";

        }

        $startItem = "
            var formscreen = \"".$this->getScreenPathOfForm($form).".jpg\";
            var statusscreen = \"".$this->getScreenPathOfResult($form).".jpg\";

            var websiteUrl = \"".$frontUrl."\";

            casper.test.begin('Test de remplissage du formulaire : ".$form->getName()."', 1, function(test) {

                casper.start( websiteUrl , function() {
                    this.echo (\"Opening website page : [\" + websiteUrl+\"]\");
                });
        ";

        $startItem .= $this->getScreenShot("formscreen");

        $endItem = "
            casper.then(function() {
                this.echo (\"Sequence : Test des messages d'erreurs \");
                console.log('Wait and check error message (3 seconds) ');
                this.wait(3000, function() {
                    casper.test.assertDoesntExist( '.formErrorContent', 'Acun message d\'erreur n\'est affiché ?' );
                });
            });";

        $endItem .= "
            casper.then(function() {
                this.echo (\"Sequence : Log de l'url de destination\");
                console.log('clicked ok, new location is ' + this.getCurrentUrl());
            });
        ";

        $endItem .= $this->getScreenShot( "statusscreen" );

        $endItem .= "
        });

        casper.run();
        ";

        return $startItem.$item.$endItem;


    }

    /**
     *
     * Method used to find Test value for a field
     * First look for attributes 'test-value' in the field
     * Then : Ask field factory to find a value for the test. The factory receive a data-type as context for the field
     *
     * @param $field
     * @return mixed
     */
    private function getValueForField ( $field ) {

        if (isset ( $field["attributes"]["test-value"] )) {
            return $field["attributes"]["test-value"];
        }

        $field_factory = $this->container->get("leadsfactory.field_factory");
        $fieldObj = $field_factory->createFromType( $field["type"] );

        if (isset($field["data-type"])) {
            $dataType = $field["data-type"];
        } else {
            $dataType = "";
        }

        return $fieldObj->getTestValue( $dataType, $field  );

    }

    /**
     * Method used to take a screenshot of the form
     * @return string
     */
    private function getScreenShot ( $fileName ) {

        $content = "
                casper.then(function() {
                    this.echo (\"Sequence : Génération de la capture d\'ecran \");
                    this.viewport (1280, 1024);
                    this.capture(".$fileName.");
                });
        ";

        return $content;

    }

    /**
     *
     * Method used to build sequences of tests.
     * A sequence define a group of fields to input at the same moment, then it
     * is possible to ask the system to pause for some ms and continue with
     * next sequence to input fields.
     * Last sequence will submit the form
     *
     * @param $fields
     * @return array
     */
    private function getSequencesToTest ( $fields ) {

        $sequencesToTest = array();
        $currentSequence = 0;

        foreach( $fields as $field ) {

            if (! isset($field["attributes"]["test-ignore"]  )) {

                if (array_key_exists("test-delay", $field["attributes"])) {
                    $currentSequence++;
                    $sequencesToTest[$currentSequence]['fields'] = array($field);
                    $sequencesToTest[$currentSequence]['delay'] = $field['attributes']['test-delay'];
                } else {
                    $sequencesToTest[$currentSequence]['fields'][] = $field;
                }

            }

        }
        return $sequencesToTest;

    }

    /**
     *
     * Method used to save the JS Casper test file
     *
     * @param $form
     * @param $content
     */
    public function saveTest ( $form, $content ) {
        /*
        if (!is_dir( "app/cache/casperjs" )) {
            mkdir ( "app/cache/casperjs" );
        }*/

        $filename = $this->getCasperScriptPath($form);
        if (is_writable($filename)) {
            $fp = fopen( $filename , 'w');
            fwrite($fp, $content);
            fclose($fp);
        } else {
            return false;
        }
        return true;

    }

    /**
     *
     * Method used to save in this object the field name and its value used for
     * the test purpose. This is required to validate the lead in the database after
     * the test
     *
     * @param $fieldName
     * @param $value
     */
    public function saveFieldValue ( $fieldName, $value ) {
        $this->fieldSet[$fieldName] = $value;
    }


    /**
     *
     * Method used to find the recorded test in history
     * This method will filter results by type of form.
     *
     * @param $fields
     */
    public function findLeadsInDatabase ( Form $form, $searchInHistoryOfNbPost = 10 ) {
        return $this->container->get("doctrine")->getRepository('TellawLeadsFactoryBundle:Leads')->findLastNByType($form, $searchInHistoryOfNbPost);
    }

    /**
     *
     * Save the final status of the form.
     * The status is saved in the formEntity
     * It saves two datas :
     *      - Status (static value of this utils class)
     *      - Log message
     *
     * @param $status
     */
    public function saveTestStatus ( $status, $log, Form $form ) {

        $form->setTestStatus( $status );
        $form->setTestLog( $log );

        $em = $this->getDoctrine()->getManager();
        $em->persist($form);
        $em->flush();

    }

    /**
     *
     * This method compare content of leads to the content used for the test
     * it return true if content is equal, false if not
     *
     * @param $fields
     * @param Array $leads
     */
    public function validateTestResults ( $fields, array $leads ) {

        //  $fields are expected fields generated for casper script
        foreach ( $leads as $lead ) {

            // Cope de travail des champs attendus
            $tmpFields = $fields;

            $this->log( "** Analyzing lead : ".$lead["id"] );

            // Decodage des données de la lead
            $content = json_decode( $lead["data"], true );
            foreach ( $tmpFields as $srcField => $srcValue ) {

                // Si la clée des données attendue existe dans la lead
                if (array_key_exists( $srcField, $content )) {

                    $this->log( "-- field exists : ".$srcField );

                    // Comparaison des valeurs attendue et de la lead
                    if ( $content[$srcField] == $srcValue ) {
                        $this->log( "-- field match : ".$srcField."/".$srcValue );
                        unset ( $tmpFields[$srcField] );
                    } else {

                        // Les valeurs ne match pas
                        $this->log( "-- field value mismatch : ".$srcField."/".$srcValue." => have : ".$content[$srcField] );
                    }

                } else{

                    // Le champs n'existe pas
                    $this->log( "-- field NOT matching : ".$srcField );
                }

            }

            // Pour chaque formulaire, on regarde si tous les champs ont été trouvés ou non
            if (count ( $tmpFields ) == 0) {
                $this->log( "-- Returning perfect MATCH " );
                return FunctionnalTestingUtils::$_VALIDATION_MATCH;
            } else if (count ( $tmpFields ) <  count ($fields)) {
                $this->log( "-- Returning partial MATCH " );
                var_dump($tmpFields);
                return FunctionnalTestingUtils::$_VALIDATION_PARTIAL_MATCH;
            }

        }

        // Si aucun retour sur les leads, c'est que nous ne trouvons rien en base, echec
        $this->log( " Returning NO MATCH " );
        return FunctionnalTestingUtils::$_VALIDATION_NO_MATCH;

    }

    /**
     * @param Form $form
     * @return array index 0 is the status 'boolean', index 1 is the output messages
     */
    private function executeCasperTest ( Form $form ) {

        $prefUtils = $this->container->get('preferences_utils');

        $pathToCasperProcess = $prefUtils->getUserPreferenceByKey('CORE_CASPER_PATH', "");
        $pathToCasperScripts = $this->getCasperScriptPath( $form );

        $command = "export PATH=$"."PATH:".$pathToCasperProcess.";".$pathToCasperProcess."casperjs test ".$pathToCasperScripts;
        $this->log( "Executing command : " . $command );

        $process = new Process( $command );
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            // throw new \Exception("Casper process is not successfull");
        }

        $output = $process->getOutput();
        $this->log( $output );

        return array ( true, $output );

    }

}

