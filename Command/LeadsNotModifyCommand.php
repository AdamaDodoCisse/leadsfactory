<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Cron\CronExpression;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\Users;


class LeadsNotModifyCommand extends ContainerAwareCommand
{


    protected function configure()
    {
        $this->setName('leadsfactory:leads:notmodify')
            ->setDescription('Leads : Not modify since 6 months');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        $nbMonths = 1;
        $arrayLeadsScope = array();

        $output->writeln('<comment>Running Leads : Not modify since '.$nbMonths.' months..</comment>');


        $users = $this->getContainer()->get('leadsfactory.users_repository')->findAll();
        $leadsRepository = $this->getContainer()->get('leadsfactory.leads_repository');
        $prefUtils = $this->getContainer()->get('preferences_utils');

        foreach ($users as $user) {
            $leads = $leadsRepository->findLastNotModify($user, $nbMonths);
            $leadsApplicationUrl = null;

            if(count($leads)) {
                if(!is_null($leads[0]->getForm()->getScope())) {
                    $scope = $leads[0]->getForm()->getScope()->getId();
                }else{
                    $scope = "";
                }

                $leadsUrl = $prefUtils->getUserPreferenceByKey(
                    'CORE_LEADSFACTORY_URL',
                    $scope
                );

                $leadsApplicationUrl = $leadsUrl;

                $returnEmail = $this->sendEmail($user, $leads, $leadsApplicationUrl, $nbMonths);
            }
        }

        $output->writeln('<comment>Done!</comment>');
    }

    private function sendEmail(Users $user, $leads, $leadsApplicationUrl, $nbMonths){

        $exportUtils = $this->getContainer()->get('export_utils');
        $subject = "Lead's Factory : Liste des DI non modifiés depuis ".$nbMonths." mois";

        $template = $this->getContainer()->get('templating')->render(
            'TellawLeadsFactoryBundle::emails/lead_not_modify_notification.html.twig',
            array(
                "user" => $user,
                "leads" => $leads,
                "nbMonths" => $nbMonths,
                "urlApplication" => $leadsApplicationUrl
            )
        );

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($exportUtils::NOTIFICATION_DEFAULT_FROM)
            ->setTo($user->getEmail())
            ->setBody($template, 'text/html');

        return $this->getContainer()->get('mailer')->send($message);
    }

}
