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


class Comundimail extends AbstractMethod {

    private $_formConfig;
    private $subjects = array(
        '1'     => "Poser une question sur une formation",
        '2'     => "Demander un programme",
        '3'     => "Demander une formation sur mesure dans vos locaux",
        '4'     => "Déposer un appel d'offres pour une formation en intra",
        '5'     => "Procéder à une inscription",
        '6'     => "Avoir des informations sur une inscription en cours",
        '7'     => "Bénéficier de réductions sur votre transport et votre hébergement",
        '8'     => "Obtenir des renseignements administratifs sur Comundi",
        '9'     => "Nous référencer",
        '10'    => 'Exercer vos droits "Données personnelles - Informatique et Libertés"',
        '11'    => 'Autre'
    );

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

        $this->_formConfig = $form->getConfig();
        $logger->info('############ COMUNDI - EXPORT ###############');

        foreach($jobs as $job) {
            $data = json_decode($job->getLead()->getData(), true); // Infos clients

            $form_subject = $data['sujet'];
            $contenu = $this->_formConfig['mails'][$form_subject]['texte'];
            $from = $this->_formConfig['mails'][$form_subject]['contact_mail'];
            $mail_contact = $this->_formConfig['mails'][$form_subject]['contact_mail'];
            $tel = $this->_formConfig['mails'][$form_subject]['tel'];
            $sujet = $this->_formConfig['mails'][$form_subject]['sujet_mail'];
            $mail_service_client = $this->_formConfig['mails'][$form_subject]['webmaster'];

            $hasError = false;
            $templatingService = $this->container->get('templating');

            if ( trim($data['origine-co']) != "" ) {
                $sujetAdv = $sujet . " - www.comundi.fr - " .$data['origine-co'];
            } else {
                $sujetAdv = $sujet;
            }

            // Envoi du mail au client
            $message_client = \Swift_Message::newInstance()
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

            $files_dir = $this->container->getParameter('kernel.root_dir').'/../datas/'.$job->getForm()->getId().'/';

            // Ajout des copies carbones
            if(isset($this->_formConfig['mails'][$form_subject]['bcc'])) {
                $message_client->addBcc($this->_formConfig['mails'][$form_subject]['bcc']);
                $logger->info('Destinataire mail BCC ajouté dans le mail client');
            }

            // Envoi du mail au service client
            $data['demande-rdv'] = $this->subjects[$data['sujet']];
            $message_service_client = \Swift_Message::newInstance()
                ->setSubject($sujetAdv)
                ->setFrom($from)
                ->setTo($mail_service_client)
                // HTML version
                ->setBody(
                    $templatingService->render(
                        'WekaLeadsExportBundle:Emails:Comundi/service_client.html.twig',
                        array(
                            'user_data' => $data
                        )
                    ),
                    'text/html'
                )
                // Plaintext version
                ->addPart(
                    $templatingService->render(
                        'WekaLeadsExportBundle:Emails:Comundi/service_client.txt.twig',
                        array(
                            'user_data' => $data
                        )
                    ),
                    'text/plain'
                )
            ;

            // Ajout des copies carbones
            if(isset($this->_formConfig['mails'][$form_subject]['bcc'])) {
                $message_service_client->addBcc($this->_formConfig['mails'][$form_subject]['bcc']);
                $logger->info('Destinataire mail BCC ajouté dans le mail service client');
            }

            // Ajout des pièces jointes
            if(isset($data['user_file'])) {
                $file = $job->getLead()->getId().'_user_file.'.substr(strrchr($data['user_file'], "."), 1);
                if(file_exists($files_dir.$file)) {
                    $message_service_client->attach(\Swift_Attachment::fromPath($files_dir.$file));
                    $logger->info('Pièce jointe "'.$files_dir.$file.'" attachée au mail service client');
                } else $logger->info('Pièce jointe introuvable : '.$files_dir.$file);
            }

            try {
                $this->container->get('mailer')->send($message_client);
                $logger->info('****** Envoi du mail client réussi ! ******');
                $this->container->get('mailer')->send($message_service_client);
                $logger->info('****** Envoi du mail service client réussi ! ******');
            } catch(\Exception $e) {
                $hasError = true;
                $logger->error("****** Erreur à l'envoi du mail de contact Comundi : ".$e->getMessage().' ******');
            }

            if($hasError) {
                $status = $exportUtils::$_EXPORT_NOT_SCHEDULED;
                $msg = 'Erreur envoi de mail Comundi';
            } else {
                $status = $exportUtils::$_EXPORT_SUCCESS;
                $msg = 'Exporté avec succès';
            }

            $logger->info('Export Comundi mail désactivé');
            $exportUtils->updateJob($job, $status, $msg);
            $exportUtils->updateLead($job->getLead(), $status, $msg);

        }
    }

}
