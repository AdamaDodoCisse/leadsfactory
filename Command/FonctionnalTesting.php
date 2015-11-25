<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $client = static::createClient();

		$forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
		$alertUtils = $this->getContainer()->get("alertes_utils");
		$formUtils = $this->getContainer()->get("form_utils");

		foreach ( $forms as $form ) {

			$fields = $formUtils->getFieldsAsArray ( $form->getSource() );

		}

	}


	private function runFormTesting ( $form, $fields ) {

	}

}