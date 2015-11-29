<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FonctionnalTestingCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('leadsfactory:testing:run')
            ->setDescription('Command running foncitonnal testing of forms.')
        ;
    }

    /**
     *
     * execute
     *
     * Method used to set yesterdays status history in database.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        $alertUtils = $this->getContainer()->get("alertes_utils");
        $formUtils = $this->getContainer()->get("form_utils");

        foreach ( $forms as $form ) {

            echo ("Traitement formulaire : ".$form->getName()."\r\n");

            if (trim($form->getUrl()) != "") {

                $testContent = $this->buildFormTest( $output, $form );
                $this->saveTest( $form, $testContent );
            }
        }

    }

    private function saveTest ( $form, $content ) {

        if (!is_dir( "app/cache/casperjs" )) {
            mkdir ( "app/cache/casperjs" );
        }

        $fp = fopen( "app/cache/casperjs/".$form->getId()."-test.js" , 'w');
        fwrite($fp, $content);
        fclose($fp);

    }

    private function buildFormTest ( $output, $form ) {

        $formUtils = $this->getContainer()->get("form_utils");

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

            $output->writeln ('Skipping form, no URL');

        } else {

            // 2/ Lecture des champs
            $fields = $formUtils->getFieldsAsArray ( $form->getSource() );

            // Build Ordered sequences for testing
            $sequencesToTest = array();
            $currentSequence = 0;
            $submit = "false";

            foreach( $fields as $field ) {

                $output->writeln ( implode (" | ", array_keys($field["attributes"]) ) );

                if ( array_key_exists("test-delay", $field["attributes"]) ) {
                    $output->writeln ('Nouvelle sequence');
                    $currentSequence++;
                    $sequencesToTest[$currentSequence]['fields'] = array( $field );
                    $sequencesToTest[$currentSequence]['delay'] = $field['attributes']['test-delay'];
                } else {
                    $output->writeln ('Pas de delai');
                    $sequencesToTest[$currentSequence]['fields'][] = $field;
                }

            }

            // Render Sequences
            foreach ( $sequencesToTest as $idx => $sequence ) {

                $item .= "
                    casper.then(function() {";

                // Adding delay to sequence if needed
                if (isset($sequence["delay"])) {

                    $item .= "
                    this.wait(".trim($sequence["delay"]).", function() {
                ";
                }

                $item .= "
                        this.echo (\"Sequence ".$idx."\");
                        this.fill(	'form[id=\"".$formId."\"]',
                            {
                                ";
                $fieldIdx = 0;
                if ($fieldIdx == count ($sequencesToTest)-1) {
                    $submit = "true";
                }
                foreach ( $sequence['fields'] as $field ) {

                    // Find value for field
                    $fieldValue = $this->getValueForField ( $field );

                    // Create field
                    $item .= "'lffield[".$field["attributes"]["id"]."]': '".$fieldValue."'";
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
                });

                casper.then(function() {
                    this.echo (\"Sequence : Log de l'url de destination\");
                    console.log('clicked ok, new location is ' + this.getCurrentUrl());
                });

                casper.then(function() {
                    this.echo (\"Sequence : Génération de la capture d\'ecran \");
                    this.capture(filename);
                });

            });

            casper.run();
            ";

            return $startItem.$item.$endItem;
        }

    }

    /**
     *
     * Method used to find Test value for a field
     *
     * @param $field
     * @return mixed
     */
    private function getValueForField ( $field ) {

        if (isset ( $field["attributes"]["test-value"] )) {
            return $field["attributes"]["test-value"];
        }

        $field_factory = $this->getContainer()->get("leadsfactory.field_factory");
        $fieldObj = $field_factory->createFromType( $field["type"] );

        if (isset($field["data-type"])) {
            $dataType = $field["data-type"];
        } else {
            $dataType = "";
        }

        return $fieldObj->getTestValue( $dataType, $field  );

    }

}