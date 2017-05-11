<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DemoModeCommand extends ContainerAwareCommand
{

    private $cronjobs = array();

    protected function configure()
    {
        $this
            ->setName('leadsfactory:demomode')
            ->setDescription('Cron Job Demo Mode data feeder')
            ->addArgument('formid', InputArgument::OPTIONAL, 'Specify form ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $formid = trim($input->getArgument('formid'));

        $this->getContainer()->get("chart")->loadDemoData($formid);

    }

}
