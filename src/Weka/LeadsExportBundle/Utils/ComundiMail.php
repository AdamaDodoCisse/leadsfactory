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
        die("export");
        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getContainer()->get('export.logger');

        $this->_formConfig = $form->getConfig();

        $logger->info('Récupération de la liste des mails destinataires');
        foreach($jobs as $job){
            $contenu = $this->_formConfig['mails'][$job->sujet]['texte'];
            $from = $this->_formConfig['mail_from'];
            $mail_contact = $this->_formConfig['mails'][$job->sujet]['contact_mail'];
            $tel = $this->_formConfig['mails'][$job->sujet]['tel'];
            $sujet = $this->_formConfig['mails'][$job->sujet]['sujet_mail'];
            $mail_webmaster = $this->_formConfig['mails'][$job->sujet]['webmaster'];

            // Upload de fichiers
            if(isset($this->_formConfig['upload_files']) && $this->_formConfig['upload_files'] == true) {
                $data = json_decode($job->getLead()->getData(), true);
            }

            $message = \Swift_Message::newInstance()
                ->setSubject($sujet)
                ->setFrom($from)
                ->setTo($job->email)
                // HTML version
                ->setBody(
                    $this->renderView(
                        'Emails/Comundi/contact.html.twig',
                        array(
                            'content' => $contenu,
                            'mail_contact' => $mail_contact,
                            'tel' => $tel,
                            'user_data' => $job,
                        )
                    ),
                    'text/html'
                )
                // Plaintext version
                ->addPart(
                    $this->renderView(
                        'Emails/Comundi/contact.txt.twig',
                        array(
                            'content' => $contenu,
                            'mail_contact' => $mail_contact,
                            'tel' => $tel,
                            'user_data' => $job,
                        )
                    ),
                    'text/plain'
                )
            ;

            if(isset($this->_formConfig['mails'][$job->sujet]['bcc'])) {
                $message->setBcc($this->_formConfig['mails'][$job->sujet]['bcc']);
            }

            try {
                $this->get('mailer')->send($message);
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
