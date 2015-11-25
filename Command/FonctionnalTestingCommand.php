<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use JonnyW\PhantomJs\Client;

class FonctionnalTestingCommand extends ContainerAwareCommand {
	
	private $cronjobs = array();
	
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

        // Find every forms
        $client = Client::getInstance();

        $logger = $this->getContainer()->get('export.logger');

		$forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
		$alertUtils = $this->getContainer()->get("alertes_utils");
		$formUtils = $this->getContainer()->get("form_utils");
        $testsUtils = $this->getContainer()->get("functionnal_testing.utils");

		foreach ( $forms as $form ) {

            $logger->info ("Form : ".$form->getCode());
			$fields = $formUtils->getFieldsAsArray ( $form->getSource() );
            $testsUtils->testForm ( $client, $form, $fields );

		}

	}


	private function runFormTesting ( $form, $fields ) {



	}

}