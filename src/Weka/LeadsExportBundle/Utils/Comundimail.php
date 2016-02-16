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
        $logger->info('Test export Comundi');

        $this->_formConfig = $form->getConfig();

        $logger->info('Récupération de la liste des mails destinataires');
        foreach($jobs as $job) {
            $data = json_decode($job->getLead()->getData(), true); // Infos clients

            $form_subject = $data['sujet'];
            $contenu = $this->_formConfig['mails'][$form_subject]['texte'];
            $from = $this->_formConfig['mail_from'];
            $mail_contact = $this->_formConfig['mails'][$form_subject]['contact_mail'];
            $tel = $this->_formConfig['mails'][$form_subject]['tel'];
            $sujet = $this->_formConfig['mails'][$form_subject]['sujet_mail'];
            $mail_service_client = $this->_formConfig['mails'][$form_subject]['webmaster'];

            $hasError = false;
            $templatingService = $this->container->get('templating');

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

            // Ajout des copies carbones
            if(isset($this->_formConfig['mails'][$form_subject]['bcc'])) {
                $message_client->addBcc($this->_formConfig['mails'][$form_subject]['bcc']);
            }

            // Ajout des pièces jointes
            $files_dir = $this->container->getParameter('kernel.root_dir').'/../datas/';
            if(isset($data['user_file']) && file_exists($files_dir.$data['user_file'])) {
                $message_client->attach(\Swift_Attachment::fromPath($files_dir.$data['user_file']));
            }


            // Envoi du mail au service client
            $data['demande-rdv'] = $this->subjects[$data['sujet']];
            $message_service_client = \Swift_Message::newInstance()
                ->setSubject($sujet)
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
            }

            // Ajout des pièces jointes
            $files_dir = $this->container->getParameter('kernel.root_dir').'/../datas/';
            if(isset($data['user_file']) && file_exists($files_dir.$data['user_file'])) {
                $message_service_client->attach(\Swift_Attachment::fromPath($files_dir.$data['user_file']));
            }

            try {
                $this->container->get('mailer')->send($message_client);
                $logger->info('****** Envoi du mail réussi ! ******');
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
