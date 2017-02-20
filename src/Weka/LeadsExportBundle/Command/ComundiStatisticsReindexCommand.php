<?php 

namespace Weka\LeadsExportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\Users;
use Tellaw\LeadsFactoryBundle\Entity\StatisticPoolElement;

class ComundiStatisticsReindexCommand extends ContainerAwareCommand {

	protected function configure() {
		$this->setName('leadsfactory:comundi:reindex-statistics')
		    ->setDescription('ReIndex daily stats for Comundi')
            ->addArgument('scopeId', InputArgument::OPTIONAL, 'Scope ID')
		;
	}

    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('export.logger');
        $searchUtils = $this->getContainer()->get('search.utils');

        $this->output = new BufferedOutput();

        $scopeId = $input->getArgument('scopeId');
        $scope = $this->getContainer()->get("doctrine")->getRepository("TellawLeadsFactoryBundle:Scope")->find( $scopeId );

        $statisticValues = $this->getContainer()->get("doctrine")
            ->getManager()
            ->createQuery('SELECT e FROM TellawLeadsFactoryBundle:StatisticPoolElement e')
            ->getResult();

        foreach ( $statisticValues as $statisticValue ) {
            $output->writeln("Exporting : ".$statisticValue->getId());
            $searchUtils->indexStatisticObject ( $statisticValue->asArray(), $scope->getCode() );
        }

	}

}