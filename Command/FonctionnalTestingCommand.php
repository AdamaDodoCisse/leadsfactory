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

            $testContent = $this->buildFormTest( $form );
            $this->saveTest( $form, $testContent );
        }

    }

    private function saveTest ( $form, $content ) {

        $fp = fopen( $form->getId()."-test.js" , 'w');
        fwrite($fp, $content);
        fclose($fp);

    }

    private function buildFormTest ( $form ) {

        $formUtils = $this->getContainer()->get("form_utils");

        // Init variables
        $sequences = array();
        $item = "";

        // Init values of test
        $frontUrl = $form->getUrl();
        $formId = "";

        if ( trim($frontUrl) == "" ) {
            $frontUrl = $this->getContainer()->generateUrl('_client_twig_preview', array('code' => $form->getCode()));
        }

        // 2/ Lecture des champs
        $fields = $formUtils->getFieldsAsArray ( $form->getSource() );

        // Build Ordered sequences for testing
        $sequencesToTest = array();
        $currentSequence = 0;
        $submit = "false";

        foreach( $fields as $field ) {

            if ( isset ($fields["attributes"]["test-delay"]) ) {
                $currentSequence++;
                $sequencesToTest[$currentSequence] = array( $field );
            } else {
                $sequencesToTest[$currentSequence][] = $field;
            }

        }

        // Render Sequences
        foreach ( $sequencesToTest as $idx => $sequence ) {

            $item .= "
				casper.then(function() {";

            // Adding delay to sequence if needed
            if (isset($field["attributes"]["test-delay"])) {

                $item .= "
				this.wait(".trim($field["attributes"]["test-delay"]).", function() {
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
            foreach ( $sequence as $field ) {

                // Find value for field
                $fieldValue = $this->getValueForField ( $field );

                // Create field
                $item .= "'lffield[".$field["attributes"]["id"]."]': '".$fieldValue."',";
                if ($fieldIdx == 0) {
                    $item.= ",";
                }
                $fieldIdx++;
            }

            $item.= "
						},
					".$submit.");";

            // Closing for delay desction
            if (isset($field["attributes"]["test-delay"])) {
                $item .= "});";
            }

            $item.= "
				});
			";



        }

        $startItem = "
			var filename = \"".$form->getId().".jpg\";
			var websiteUrl = \"".$frontUrl."\";

			casper.test.begin('assertDoesntExist() formulaire : ".$form->getName()."', 1, function(test) {

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
        $field = $field_factory->createFromType( $field["type"] );

        if (isset($field["data-type"])) {
            $dataType = $field["data-type"];
        } else {
            $dataType = "";
        }

        return $field->getTestValue( $dataType  );

    }

}