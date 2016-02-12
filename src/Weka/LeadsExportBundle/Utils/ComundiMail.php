<?php
/**
 * Created by PhpStorm.
 * User: evans
 * Date: 03/02/16
 * Time: 14:14
 */

namespace Weka\LeadsExportBundle\Utils;

use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;


class ComundiMail extends AbstractMethod {

    private $_formConfig;

    public function __construct()
    {
    }

    /**
     * Process export
     *
     * @param array $jobs
     * @param Form $form
     */
    public function export($jobs, $form)
    {
        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getContainer()->get('export.logger');
        $logger->info('Test export Comundi');

        $this->_formConfig = $form->getConfig();

        $logger->info('Récupération de la liste des mails destinataires');
        foreach($jobs as $job){
            $data = json_decode($job->getLead()->getData(), true); // Infos clients

            $form_subject = $data['sujet'];
            $contenu = $this->_formConfig['mails'][$form_subject]['texte'];
            $from = $this->_formConfig['mail_from'];
            $mail_contact = $this->_formConfig['mails'][$form_subject]['contact_mail'];
            $tel = $this->_formConfig['mails'][$form_subject]['tel'];
            $sujet = $this->_formConfig['mails'][$form_subject]['sujet_mail'];
            $mail_webmaster = $this->_formConfig['mails'][$form_subject]['webmaster'];

            // Upload de fichiers
            if(isset($this->_formConfig['upload_files']) && $this->_formConfig['upload_files'] == true) {
            }

            $templatingService = $this->container->get('templating');
            $message = \Swift_Message::newInstance()
                ->setSubject($sujet)
                ->setFrom($from)
                ->setTo($data['email'])
                // HTML version
                ->setBody(
                    $templatingService->render(
                        'WekaLeadsExportBundle:Emails:Comundi/contact.html.twig',
                        array(
                            'content' => $contenu,
                            'mail_contact' => $mail_contact,
                            'tel' => $tel,
                            'user_data' => $data,
                        )
                    ),
                    'text/html'
                )
                // Plaintext version
                ->addPart(
                    $templatingService->render(
                        'WekaLeadsExportBundle:Emails:Comundi/contact.txt.twig',
                        array(
                            'content' => $contenu,
                            'mail_contact' => $mail_contact,
                            'tel' => $tel,
                            'user_data' => $data,
                        )
                    ),
                    'text/plain'
                )
            ;

            if(isset($this->_formConfig['mails'][$form_subject]['bcc'])) {
                $message->setBcc($this->_formConfig['mails'][$form_subject]['bcc']);
            }

            try {
                $this->container->get('mailer')->send($message);
                $logger->info('****** Envoi du mail réussi ! ******');
            } catch(\Exception $e) {
                $logger->error("****** Erreur à l'envoi du mail de contact Comundi : ".$e->getMessage().' ******');
            }

            $logger->info('Export Comundi mail désactivé');
            $exportUtils->updateJob($job, $exportUtils::$_EXPORT_NOT_SCHEDULED, 'Export Comundi mail désactivé');
            $exportUtils->updateLead($job->getLead(), $exportUtils::$_EXPORT_NOT_SCHEDULED, 'Export Comundi mail désactivé');

        }
    }

}
