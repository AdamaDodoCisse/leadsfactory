<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CronRunnerCommand extends ContainerAwareCommand {
	
	private $cronjobs = array();
	
	protected function configure()
	{
		$this
		->setName('leadsfactory:cronjobs:alerts')
		->setDescription('Cron Job ALERT LeadsFactorty')
		->addArgument('mode', InputArgument::OPTIONAL, 'set to true to force cronjob execution')
		;
	}

    protected function execute(InputInterface $input, OutputInterface $output)
	{
		$isForced = $input->getArgument("mode");
		if ($isForced) echo ("Force mode activated\r\n");

		// First Iterate over Types


        // Second iterate over Forms

        // Done

	}

    public function addCronjob ( $cronjobService, $alias ) {
		$this->cronjobs[$alias] = $cronjobService;
	}

    private function getTypes () {
        $types = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->findAll();
    }

    private function getForms () {
        $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
    }

    private function getDayValueForType ( $offset = 0 ) {

    }


}