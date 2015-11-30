<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\KibanaSearch;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Tellaw\LeadsFactoryBundle\Entity\SearchResult;
use Tellaw\LeadsFactoryBundle\Entity\UserPreferences;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Tellaw\LeadsFactoryBundle\Shared\SearchShared;

class FunctionnalTestingUtils extends SearchShared {


    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    private $logger;


    public function __construct () {


    }

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    public function testForm ( $client, $form, $fields ) {

        $this->createJasperScript ( $form );

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
            //
        } else {

            // 2/ Lecture des champs
            $fields = $formUtils->getFieldsAsArray ( $form->getSource() );

            // Build Ordered sequences for testing
            $submit = "false";

            // Get the correct sequence of fields to test
            $sequencesToTest = $this->getSequencesToTest( $fields );

            // Render Sequences
            $nbSequences = count ($sequencesToTest);
            $sequenceIdx = 1;
            foreach ( $sequencesToTest as $idx => $sequence ) {

                if ($sequenceIdx == count ($sequencesToTest)) {
                    $submit = "true";
                }
                $sequenceIdx++;

                $item .= $this->getScreenShot();

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
                $fieldIdx = 0;
                foreach ( $sequence['fields'] as $field ) {

                    // Find value for field
                    $fieldValue = $this->getValueForField ( $field );

                    // Create field
                    if ( isset( $field["attributes"]["test-alias"] ) ) {
                        $item .= "'lffield[".$field["attributes"]["test-alias"]."]': '".$fieldValue."'";
                    } else {
                        $item .= "'lffield[".$field["attributes"]["id"]."]': '".$fieldValue."'";
                    }
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
                var filename = \"".$form->getId().".jpg\";
                var websiteUrl = \"".$frontUrl."\";

                casper.test.begin('Test de remplissage du formulaire : ".$form->getName()."', 1, function(test) {

                    casper.start( websiteUrl , function() {
                        this.echo (\"Opening website page : [\" + websiteUrl+\"]\");
                    });
            ";

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

            $endItem .= $this->getScreenShot();

            $endItem .= "
            });

            casper.run();
            ";

            return $startItem.$item.$endItem;
        }

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
    private function getScreenShot (  ) {

        $content = "
                casper.then(function() {
                    this.echo (\"Sequence : Génération de la capture d\'ecran \");
                    this.capture(filename);
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

    public function saveTest ( $form, $content ) {
        if (!is_dir( "app/cache/casperjs" )) {
            mkdir ( "app/cache/casperjs" );
        }
        $fp = fopen( "app/cache/casperjs/".$form->getId()."-test.js" , 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

}

