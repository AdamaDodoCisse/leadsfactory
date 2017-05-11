<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Leads;

class FunctionnalTestingUtils implements ContainerAwareInterface
{

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

    public function __construct()
    {

        PreferencesUtils::registerKey("CORE_LEADSFACTORY_URL",
            "Url de l'application, sur le scope global pour le BO, et sur les scopes pour les formulaires",
            PreferencesUtils::$_PRIORITY_OPTIONNAL
        );

        PreferencesUtils::registerKey("CORE_CASPER_PATH",
            "Path to Casper install for functionnal testings.",
            PreferencesUtils::$_PRIORITY_OPTIONNAL);

    }

    /**
     * Method used to detect if recorded lead has been created by a test or not
     * @param Leads $lead
     * @return bool
     */
    public function isTestLead(Leads $lead)
    {
        if (stristr($lead->getUserAgent(), 'phantomjs')) {
            return true;
        }

        return false;
    }

    public function setIsWebMode($mode)
    {
        $this->isWebMode = $mode;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function setOutputInterface($outputInterface)
    {
        $this->outputInterface = $outputInterface;
    }

    public function isFormTestable(Form $form)
    {
        $formConfig = $form->getConfig();
        if (isset($formConfig ["configuration"]["functionnalTestingEnabled"]) && $formConfig ["configuration"]["functionnalTestingEnabled"] == true) {
            return true;
        } else {
            return false;
        }
    }

    public function log($msg)
    {

        if ($this->logger != null) {
            $this->logger->info($msg . "\n");
            $this->logContent .= $msg . "<br />";
            \flush();
        }
        if ($this->outputInterface != null) {
            $this->outputInterface->writeln($msg);
        }

    }

    public function runByStep($step, Form $form, $status = 0, $log = "", $resultOfTheTest = "")
    {

        switch ($step) {
            case FunctionnalTestingUtils::$_STEP_1_CREATE_CASPER_SCRIPT:

                // 1/ Check or create Jasper file for testing
                $testContent = $this->createCasperScript($form);

                return $this->saveTest($form, $testContent);
                break;
            case FunctionnalTestingUtils::$_STEP_2_EXECUTE_CASPER_SCRIPT:
                // 2/ Run Casper test
                if ($this->isCasperScriptExist($form)) {
                    list ($status, $log) = $this->executeCasperTest($form);

                    return array($status, $log);
                } else {
                    throw new \Exception ("Issue while creating CASPER Script");
                }
                break;
            case FunctionnalTestingUtils::$_STEP_3_EVALUATE_LEADS:
                // 3/ Find in leads the test result
                if ($status) {
                    $leads = $this->findLeadsInDatabase($form);
                    $resultOfTheTest = $this->validateTestResults($this->fieldSet, $leads);

                    return $resultOfTheTest;
                }
                break;
            case FunctionnalTestingUtils::$_STEP_4_PERSIST_RESULTS:
                // 4/ Save status of test
                $form->setTestStatus($resultOfTheTest);
                $form->setTestLog($this->logContent);
                $this->log("-- Saving test result");
                $em = $this->container->get("doctrine")->getManager();
                $em->persist($form);
                $em->flush();
                break;
        }

    }

    public function run(Form $form)
    {

        // Step 0
        $this->pretest_operations();

        // Step 1
        $status = $this->runByStep(FunctionnalTestingUtils::$_STEP_1_CREATE_CASPER_SCRIPT, $form);

        if (!$status) {
            throw new \Exception ("Unable to create casper script");
        }

        // Step 2
        list ($status, $log) = $this->runByStep(FunctionnalTestingUtils::$_STEP_2_EXECUTE_CASPER_SCRIPT, $form);

        // Step 3
        $statusOfTest = $this->runByStep(FunctionnalTestingUtils::$_STEP_3_EVALUATE_LEADS, $form, $status, $log);

        // Step 4
        $this->runByStep(FunctionnalTestingUtils::$_STEP_4_PERSIST_RESULTS, $form, $status, $log, $statusOfTest);

    }

    /**
     *
     * Method used to get the path and name of the screenshot file
     * This is for the screenshot of the form
     *
     * @param Form $form
     * @param bool $ask_url
     * @return null|string [String] path and filename
     * @internal param Object $Form targeted by the screenshot
     */
    public function getScreenPathOfForm(Form $form, $ask_url = false)
    {
        return $this->getScreenshotPath("form", $form, $ask_url);
    }

    /**
     *
     * Method used to get the path and name of the screenshot file
     * This is for the screenshot of the result
     *
     * @param Form $form
     * @param bool $ask_url
     * @return null|string [String] path and filename
     * @internal param Object $Form targeted by the screenshot
     */
    public function getScreenPathOfResult(Form $form, $ask_url = false)
    {
        return $this->getScreenshotPath("result", $form, $ask_url);
    }

    /**
     * @param $type
     * @param Form $form
     * @param $ask_url
     * @return null|string
     */
    private function getScreenshotPath($type, Form $form, $ask_url)
    {
        // ex : /var/www/weka-leadsfactory/app
        $base_dir = $this->container->get('kernel')->getRootDir();
        $screenshotDir = $base_dir . "/../web/screenshots";
        $path = "$screenshotDir/$type-" . $form->getId() . ".jpg";

        if ($ask_url) {
            $base_url = $this->container->get('router')->getContext()->getBaseUrl();
            $url = "$base_url/screenshots/$type-" . $form->getId() . ".jpg";
            if (!file_exists($path)) return null;

            return $url;
        } else {
            if (!is_dir($screenshotDir)) {
                \mkdir($screenshotDir);
            }

            return $path;
        }
    }

    /**
     *
     * Used to inject Symfony container to application
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    public function testForm($client, $form, $fields)
    {
        $this->createCasperScript($form);
    }

    private function getCasperScriptPath(Form $form)
    {

        // ex : /var/www/weka-leadsfactory/app
        $base = $this->container->get('kernel')->getRootDir();

        return $base . "/cache/casperjs/" . $form->getId() . "-test.js";
    }

    /**
     *
     * Checks if the casper script test file exists
     *
     * @param Form $form
     * @return bool
     * @internal param Object $Form to test with Casper
     */
    public function isCasperScriptExist(Form $form)
    {

        if (file_exists($this->getCasperScriptPath($form))) {
            return true;
        }

        return false;
    }


    private function update_field_by_code($code, $value, $scope = "default")
    {
        $field = $this->container->get("doctrine")->getRepository('TellawLeadsFactoryBundle:Field')->findOneByCode($code);
        $data = json_decode($field->getTestValue(), true);
        if ($data) {
            $data[$scope] = $value;
            $field->setTestValue(json_encode($data));
            $em = $this->container->get("doctrine")->getEntityManager();
            $em->persist($field);
            $em->flush();

            return true;
        }

        return false;
    }

    private function pretest_operations()
    {
        // update twillio code
        $twillio_code = round(intval(date("dm")) / 2, 0, PHP_ROUND_HALF_DOWN);
        $this->update_field_by_code("twilio_validation", (String)$twillio_code);
    }

    /**
     * Special transformations for fields
     * @param $fieldName
     * @param $value
     * @return string
     * @internal param $fields
     */
    private function fieldsSpecificTreatment($fieldName, $value)
    {
        return $value;
    }

    /**
     *
     * Method used to generate JasperJS Script for Testing
     *
     * @param $form
     * @return string
     * @throws \Exception
     */
    public function createCasperScript($form)
    {

        $formUtils = $this->container->get("form_utils");
        $fields = array();

        $formConfig = $form->getConfig();

        if (isset($formConfig ["configuration"]["formId"])) {
            $formId = $formConfig ["configuration"]["formId"];
        } else {
            $this->log("### Attention : Veuillez rajouter un \"formId\" dans la fonfiguration!");
            $formId = "leadsfactory-form";
        }
        $this->log("FORMID : $formId");


        // Init variables
        $sequences = array();
        $item = "";

        // Init values of test
        $frontUrl = $form->getUrl();
        if (trim($frontUrl) == "") {

            // build preview URL
            $prefUtils = $this->container->get('preferences_utils');
            $leadsUrl = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', "");

            if (trim($leadsUrl) == "") {
                throw new \Exception ("Lead's Factory URL not set in preference : CORE_LEADSFACTORY_URL");
            }

            $frontUrl = $leadsUrl . "client/preview/twig/" . $form->getCode();
            $this->log("Using preview url : " . $frontUrl);
        } else {
            $this->log("Using declared url : " . $frontUrl);
        }

        // 2/ Lecture des champs
        $fields = $formUtils->getFieldsAsArray($form->getSource());

        // Build Ordered sequences for testing
        $submit = "false";

        // Get the correct sequence of fields to test
        $sequencesToTest = $this->getSequencesToTest($fields);

        // Get a screenshot of the form
        $this->getScreenShot("formscreen");

        // Render Sequences
        $nbSequences = count($sequencesToTest);
        $sequenceIdx = 1;
        foreach ($sequencesToTest as $idx => $sequence) {

            // set delay  for every thing
            ///!\ MUST CHECK
            $sequence["delay"] = "100";

            if ($sequenceIdx == count($sequence['fields'])) {
                $submit = "true";
            }

            $item .= $this->getScreenShot("statusscreen");
            $item .= "casper.then(function() {\n\t";

            // Loop over fields to add test values to the casperjs file
            // It also saves fields and value to this class for test validation
            $fieldIdx = 0;
            $item .= "this.echo (\"Sequence " . ($idx + 1) . "/" . $nbSequences . "\");\n\t";

            foreach ($sequence['fields'] as $field) {
                $sequenceIdx++;
                $item .= "this.wait(" . trim($sequence["delay"]) . ", function() {\n\t";
                $item .= "this.fill('form[id=\"" . $formId . "\"]',\n\t{";

                // Find value for field
                $fieldArr = $this->getValueForField($field);
                $scope = $form->getScope()->getCode();
                $fieldValue = "";

                // Get default value if there is no one for scope
                if (isset($fieldArr[$scope]))
                    $fieldValue = empty($fieldArr[$scope]) ? $fieldArr["default"] : $fieldArr[$scope];

                // Create field
                if (isset($field["attributes"]["test-alias"])) {
                    $fieldName = $field["attributes"]["test-alias"];
                } else {
                    $fieldName = $field["attributes"]["id"];
                }
                $item .= "'lffield[" . $fieldName . "]' : '" . $fieldValue . "'";

                // Saves to this object value for later verification
                $this->saveFieldValue($fieldName, $fieldValue);

                $fieldIdx++;
                if ($fieldIdx == count($sequence['fields'])) $submit = "true";
                $item .= "}, " . $submit . ");";
                $item .= "});\n";
            }
            $item .= "});\n\n";

        }
        $item .= "});\n\n";

        $startItem = "\nvar formscreen = \"" . $this->getScreenPathOfForm($form) . "\";\n" .
            "var statusscreen = \"" . $this->getScreenPathOfResult($form) . "\";\n" .
            "var websiteUrl = \"" . $frontUrl . "\";\n\n" .
            "casper.test.begin('Test de remplissage du formulaire : " . $form->getName() . "', 1, function(test) {\n\t" .
            "casper.start( websiteUrl , function() {\n\t" .
            "this.echo (\"Opening website page : [\" + websiteUrl+\"]\");\n" .
            "});\n\n";
        $startItem .= $this->getScreenShot("formscreen");

        $endItem = "casper.then(function() {\n\t" .
            "this.echo (\"Sequence : Test des messages d'erreurs \");\n\t" .
            "console.log('Wait and check error message (2 seconds) ');\n\t" .
            "this.wait(2000, function() {\n\t" .
            "\tif (casper.exists('.formErrorContent')){console.log('Erreurs de saisie.'); console.log(casper.getHTML('.formErrorContent'));};\n\t" .
            "});\n" .
            "});\n\n";
        $endItem .= "casper.then(function() {\n\t" .
            "this.echo (\"Sequence : Log de l'url de destination\");\n\t" .
            "console.log('clicked ok, new location is ' + this.getCurrentUrl());\n" .
            "});\n\n";
        $endItem .= $this->getScreenShot("statusscreen");
        $endItem .= "casper.run();\n";

        return $startItem . $item . $endItem;
    }

    /**
     *
     * Method used to find Test value for a field
     * First look for attributes 'testValue' in the field
     * Then : Ask field factory to find a value for the test. The factory receive a data-type as context for the field
     *
     * Latest Edit : Request from Fields entity list
     * @param $field
     * @return mixed
     */
    private function getValueForField($field)
    {

        $dfield = $this->container->get("doctrine")->getRepository('TellawLeadsFactoryBundle:Field')->findOneByCode($field["attributes"]['id']);
        if ($dfield)
            return $dfield->getValues();

        return null;
    }

    /**
     * Method used to take a screenshot of the form
     * @return string
     */
    private function getScreenShot($fileName)
    {

        $content = "casper.then(function() {\n\t" .
            "this.echo (\"Sequence : Génération de la capture d'écran \");\n\t" .
            "this.viewport (1280, 1024);\n\t" .
            "this.capture(" . $fileName . ");\n" .
            "});\n\n";


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
    private function getSequencesToTest($fields)
    {

        $sequencesToTest = array();
        $currentSequence = 0;

        foreach ($fields as $field) {

            if (!isset($field["attributes"]["test-ignore"])) {

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
     * Method used to save the JS Casper test file
     *
     * @param $form
     * @param $content
     * @return bool
     */
    public function saveTest($form, $content)
    {

        if (!is_dir($this->container->get('kernel')->getRootDir() . '/cache/casperjs')) {
            mkdir($this->container->get('kernel')->getRootDir() . '/cache/casperjs');
        }

        $filename = $this->getCasperScriptPath($form);
        $this->log("FILE : " . $filename);

        if (!$fp = fopen($filename, 'w')) {
            $this->log("Unable to write file : " . $filename);

            return false;
        }

        if (fwrite($fp, $content) === FALSE) {
            $this->log("Impossible d'ecrire le contenu du fichier dans : " . $filename);

            return false;
        }
        fclose($fp);

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
    public function saveFieldValue($fieldName, $value)
    {
        $this->fieldSet[$fieldName] = $this->fieldsSpecificTreatment($fieldName, $value);
    }


    /**
     *
     * Method used to find the recorded test in history
     * This method will filter results by type of form.
     *
     * @param Form $form
     * @param int $searchInHistoryOfNbPost
     * @return
     * @internal param $fields
     */
    public function findLeadsInDatabase(Form $form, $searchInHistoryOfNbPost = 10)
    {
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
    public function saveTestStatus($status, $log, Form $form)
    {

        $form->setTestStatus($status);
        $form->setTestLog($log);

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
     * @param array|Array $leads
     * @return int
     */
    public function validateTestResults($fields, array $leads)
    {

        //  $fields are expected fields generated for casper script
        foreach ($leads as $lead) {

            // Cope de travail des champs attendus
            $tmpFields = $fields;
            $this->log('LEAD ID: "' . $lead["id"] . '"');
            $this->log("--");
            // Decodage des données de la lead
            $content = json_decode($lead["data"], true);
            foreach ($tmpFields as $srcField => $srcValue) {
                // Si la clée des données attendue existe dans la lead
                if (array_key_exists($srcField, $content)) {
                    // Comparaison des valeurs attendue et de la lead
                    if ($content[$srcField] == $srcValue) {
                        $this->log('[ok] Field found : "' . $srcField . '"');
                        $this->log('>>> "' . $srcValue . '"');
                        unset ($tmpFields[$srcField]);
                    } else { // 
                        $this->log('[ko] Field found : "' . $srcField . '"');
                        $this->log('>>> "' . $srcValue . '"');
                        $this->log('<<< "' . $content[$srcField] . '"');
                    }
                } else { // Le champs n'existe pas
                    $this->log("Field not found : " . $srcField);
                }
                $this->log("--");
            }

            // Pour chaque formulaire, on regarde si tous les champs ont été trouvés ou non
            if (count($tmpFields) == 0) {
                $this->log("PERFECT MATCH ");

                return FunctionnalTestingUtils::$_VALIDATION_MATCH;
            } else if (count($tmpFields) < count($fields)) {
                $this->log("PARTIAL MATCH ");
                echo "<pre>";
                echo($tmpFields . "\r\n");
                echo "</pre>";

                return FunctionnalTestingUtils::$_VALIDATION_PARTIAL_MATCH;
            }

        }

        // Si aucun retour sur les leads, c'est que nous ne trouvons rien en base, echec
        $this->log(" Returning NO MATCH ");

        return FunctionnalTestingUtils::$_VALIDATION_NO_MATCH;

    }

    /**
     * @param Form $form
     * @return array index 0 is the status 'boolean', index 1 is the output messages
     */
    private function executeCasperTest(Form $form)
    {

        $prefUtils = $this->container->get('preferences_utils');

        $pathToCasperProcess = $prefUtils->getUserPreferenceByKey('CORE_CASPER_PATH', "");
        $pathToCasperScripts = $this->getCasperScriptPath($form);

        $command = "export PATH=$" . "PATH:" . $pathToCasperProcess . ";" . $pathToCasperProcess . "casperjs test " . $pathToCasperScripts;
        $this->log("Executing command : " . $command);

        $process = new Process($command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            // throw new \Exception("Casper process is not successfull");
        }

        $output = $process->getOutput();
        $this->log($output);

        return array(true, $output);

    }

}

