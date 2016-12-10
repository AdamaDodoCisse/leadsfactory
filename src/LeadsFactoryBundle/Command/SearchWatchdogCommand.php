<?php

namespace LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use LeadsFactoryBundle\Utils\ElasticSearchUtils;

class SearchWatchdogCommand extends ContainerAwareCommand
{

    private $cronjobs = array();

    protected function configure()
    {
        $this
            ->setName('leadsfactory:search:watchdog')
            ->setDescription('Cron Job watchdog for search process')
            ->addArgument('formid', InputArgument::OPTIONAL, 'Specify form ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $request = '/_status';
        $searchUtils = $this->getContainer()->get("search.utils");

        $response = $searchUtils->request(ElasticSearchUtils::$PROTOCOL_GET, $request);

        if (trim($response) == "") {

            $output->writeln('Search process is not alive, waiking it up now!');

            $searchUtils->start();

            $output->writeln('process must be started now.');

        } else {

            $output->writeln('Search process is alive');

        }

    }

}
