<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Monolog\Logger;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\StatusHistory;
use Symfony\Component\Security\Acl\Exception\Exception;

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
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = new Logger("STATUS");
        $results = array();
        $scope = array();
        // Find every forms
        $forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        $alertUtils = $this->getContainer()->get("alertes_utils");

        $minDate = new \DateTime();
        $currentDate = new \DateTime();
        $minDate = $minDate->sub(new \DateInterval("P01D"));

        // Check theirs statuses for previous day
        foreach ($forms as $form) {
            $result = array();
            $alertUtils->setValuesForAlerts ( $form );

            $logger->info("Recherche du status du formulaire : ".$form->getName()." / Id : ".$form->getId());
            // 1 - Load a potentially existing status from history :
            // If so, just update it.

            try {

                $statusHistory = $this->getContainer()->get("doctrine")
                                ->getManager()->getRepository('TellawLeadsFactoryBundle:StatusHistory')
                                ->findByStatusDateAndForm( $minDate, $form );
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

                $logger->info("Status enregistré : ".$form->yesterdayStatus);

                if ($form->getScope()) {
                    $result['id'] = $form->getId();
                    $result['name'] = $form->getName();
                    $result['status'] = $form->yesterdayStatus;
                    $result['status_comment'] = $form->yesterdayStatusText;

                    $scope[$form->getScope()->getId()] = $form->getScope()->getName();
                    $results[$form->getScope()->getId()][$form->yesterdayStatus][] = $result;
                }

            }  catch (\Exception $e) {
                $logger->error('ERROR for form :'.$form->getName().'/ id : '.$form->getId());
                $logger->error($e->getTraceAsString());
            }

        }
        $this->sendStatusLogsMail($results, $scope);
	}


    private function sendStatusLogsMail($results, $scopes) {
        $logger = new Logger("STATUS");
        $exportUtils = $this->getContainer()->get('export_utils');
        $prefs = $this->getContainer()->get('leadsfactory.preference_repository');
        $templatingService = $this->getContainer()->get('templating');

        foreach ($results as $id => $scope) {

            $email = $prefs->findByKeyAndScope('CORE_STATUS_HISTORY_EMAIL', $id);
            $title = "[LEADS Factory] Status des formulaires : ".$scopes[$id];
            $from = $exportUtils::NOTIFICATION_DEFAULT_FROM;
            if (count($scope)) {
                $body = $templatingService->render('TellawLeadsFactoryBundle:emails:status_history_task.html.twig', array("results" => $scope, "scope" => $scopes[$id]));
                if (is_array($email) && count($email) && $email[0]['p_value']) {
                    $email = explode(';', $email[0]['p_value']);
                    foreach ($email as $s_email) {
                        $message = Swift_Message::newInstance()
                            ->setSubject($title)
                            ->setFrom($from)
                            ->setTo($s_email)
                            ->setBody($body)
                            ->setContentType("text/html");
                        $logger->info($scopes[$id]." : Envoie du mail à : " . $s_email);
                        try {
                            $this->getContainer()->get('mailer')->send($message);
                        } catch(Exception $e){
                            $logger->error($e->getMessage());
                        }
                    }

                } else {
                    $logger->info("Email d'export introuvalble : ".$scopes[$id]);
                }
            }
        }
    }

}