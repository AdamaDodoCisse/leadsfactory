<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\StatusHistory;

class StatusHistoryUpdateCommand extends ContainerAwareCommand {
	
	private $cronjobs = array();
	
	protected function configure() {
		$this
		->setName('leadsfactory:statusHistory:update')
		->setDescription('Command updating statuses history.')
		//->addArgument('mode', InputArgument::OPTIONAL, 'set to true to force cronjob execution')
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
        $forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        $alertUtils = $this->getContainer()->get("alertes_utils");

        $minDate = new \DateTime();
        $currentDate = new \DateTime();
        $minDate = $minDate->sub(new \DateInterval("P01D"));

        // Check theirs statuses for previous day
        foreach ($forms as $form) {

            $alertUtils->setValuesForAlerts ( $form );

            $output->writeln("Recherche du status du formulaire : ".$form->getName()." / Id : ".$form->getId());

            // 1 - Load a potentially existing status from history :
            // If so, just update it.

            try {

                $statusHistory = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:StatusHistory')->findByStatusDateAndForm( $minDate, $form );
                if (count ($statusHistory)>0) {
                    $statusHistory = $statusHistory[0];
                } else {

                    $statusHistory = new StatusHistory();
                    $statusHistory->setStatusDate( $minDate );
                    $statusHistory->setForm( $form );
                    $statusHistory->setData( '' );
                    $statusHistory->setCreatedAt( $currentDate );

                }

                $statusHistory->setStatus( $form->yesterdayStatus );
                $statusHistory->setUpdatedAt( $currentDate );

                $em = $this->getContainer()->get("doctrine")->getManager();
                $em->persist($statusHistory);
                $em->flush();

                $output->writeln("<info>Status enregistré : ".$form->yesterdayStatus."</info>");

            }  catch (\Exception $e) {

                $output->writeln('<error>ERROR for form :'.$form->getName().'/ id : '.$form->getId().'</error>');
                $output->writeln('<error>'.$e->getTraceAsString().'</error>');

            }
        }


	}


}