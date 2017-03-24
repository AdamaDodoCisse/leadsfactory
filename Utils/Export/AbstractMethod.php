<?php

namespace Tellaw\LeadsFactoryBundle\Utils\Export;

use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;


abstract class AbstractMethod
{

    protected $exportDir = 'Export';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /*
     *
     */
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    abstract protected function export($jobs, $form);

    /**
     * Retourne le chemin du dossier d'export
     *
     * @throws \Exception
     * @return string
     */
    protected function getExportPath()
    {
        $basePath = $this->getContainer()->get('kernel')->getRootDir();
        $path = $basePath . DIRECTORY_SEPARATOR . $this->exportDir;

        if (!file_exists($path)) {
            if (!mkdir($path, '0755')) {
                throw new \Exception('Error : can\'t create the export directory');
            }
        }

        return $path;
    }

    /**
     * Teste la validité de l'email
     *
     * @param $lead
     * @param $email
     *
     * @return mixed
     */
    public function isEmailValidated($lead, $email)
    {
        return $this->getContainer()->get('leadsfactory.client_email_repository')->isEmailValidated($email);
    }

    /**
     * @param $reason
     * @param $form
     * @param $export
     * @param $currentStatus
     * @param $newStatus
     */
    protected function notifyOfExportIssue($reason, $form, $job, $newStatus, $forceNotification = false)
    {

        if ($job->getStatus() == ExportUtils::$_EXPORT_ONE_TRY_ERROR || $forceNotification) {

            $logger = $this->getContainer()->get('export.logger');

            $templatingService = $this->container->get('templating');
            $dest = $this->container->getParameter("export_notification_dest");

            $message = \Swift_Message::newInstance()
                ->setSubject('Lead\'s : Incident d\'export')
                ->setFrom($this->container->getParameter("export_notification_from"))
                ->setTo($dest)
                ->setBody($templatingService->render('TellawLeadsFactoryBundle:emails:export_notification.txt.twig', array('reason' => $reason, 'form' => $form, 'job' => $job, 'status' => $newStatus)));
            $this->container->get('mailer')->send($message);

            $logger->info("Notification mail sent to  : " . $dest);

        }

    }

} 
