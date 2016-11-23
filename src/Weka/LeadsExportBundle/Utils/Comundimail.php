<?php
/**
 * Created by PhpStorm.
 * User: evans
 * Date: 03/02/16
 * Time: 14:14
 */

namespace Weka\LeadsExportBundle\Utils;


use Tellaw\LeadsFactoryBundle\Entity\Users;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;
use Tellaw\LeadsFactoryBundle\Utils\PreferencesUtils;


class Comundimail extends AbstractMethod {

    private $_formConfig;
    private $subjects = array(
        "1" => "Poser une question sur une formation",
        "2" => "Demander un programme",
        "3" => "Demander une formation sur mesure dans vos locaux",
        "4" => "Déposer un appel d'offres pour une formation en intra",
        "5" => "Procéder à une inscription",
        "6" => "Avoir des informations sur une inscription en cours",
        "7" => "Bénéficier de réductions sur votre hébergement",
        "8" => "Obtenir des renseignements administratifs sur Comundi",
        "9" => "Nous référencer",
        "10" => 'Exercer vos droits "Données personnelles - Informatique et Libertés" ',
        "11" => "Autre",
        "12" => "Demande d'information pour Comundi Consulting",
        "13" => "Demande d'information sur l'externalisation des entretiens professionnels",
        "14" => "Demande d'information ou devis pour le MOOC",
        "15" => "Demande d'information pour Comundimix",
        "16" => "Demande d'information sur l'actualité",
        "17" => "Demande d'information sur le coaching",
        "18" => "Demande d'information"
    );

    public function __construct()
    {

        PreferencesUtils::registerKey( "CORE_LEADSFACTORY_EMAIL_SENDER",
            "Email used by the lead's factory as sender in emails",
            PreferencesUtils::$_PRIORITY_OPTIONNAL
        );

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
        $testUtils = $this->container->get("functionnal_testing.utils");

        $this->_formConfig = $form->getConfig();
        $logger->info('############ COMUNDI - EXPORT ###############');

        foreach($jobs as $job) {

            $data = json_decode($job->getLead()->getData(), true); // Infos clients
            $logger->info('[ComundiMail] - Traitement Lead : '.$job->getLead()->getId());
            $form_subject = $data['sujet'];
            $hasError = false;

            // Verifier si le job doit être traité
            if ($testUtils->isTestLead($job->getLead())) {
                $exportUtils->updateJob($job, ExportUtils::$_EXPORT_NOT_SCHEDULED, 'TEST - pas d\'export');
                $exportUtils->updateLead($job->getLead(), ExportUtils::$_EXPORT_NOT_SCHEDULED, 'TEST - pas d\'export');
                continue;
            }

            if ( $this->_formConfig["mails"][$form_subject]["mode"] == "mail" ) {

                // Mode envoi d'email, VS mode CRM Affectation

                $contenu = $this->_formConfig['mails'][$form_subject]['texte'];
                $from = $this->_formConfig['mails'][$form_subject]['contact_mail'];
                $mail_contact = $this->_formConfig['mails'][$form_subject]['contact_mail'];
                $tel = $this->_formConfig['mails'][$form_subject]['tel'];
                $sujet = $this->_formConfig['mails'][$form_subject]['sujet_mail'];
                $mail_service_client = $this->_formConfig['mails'][$form_subject]['webmaster'];

                $templatingService = $this->getContainer()->get('templating');

                if ( trim($data['origine-co']) != "" ) {
                    $sujetAdv = $sujet . " - www.comundi.fr - " .$data['origine-co'];
                } else {
                    $sujetAdv = $sujet;
                }

                // Envoi du mail au client
                $logger->info('[ComundiMail] - Envoi mail client / Lead : '.$job->getLead()->getId(). ' / To '.$data['email'].' / Sujet : ' . $sujet. ' / From : '.$from);
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

                $files_dir = $this->getContainer()->getParameter('kernel.root_dir').'/../datas/'.$job->getForm()->getId().'/';

                // Ajout des copies carbones
                if(isset($this->_formConfig['mails'][$form_subject]['bcc'])) {
                    $logger->info('[ComundiMail] -Ajotu BCC ('.$this->_formConfig['mails'][$form_subject]['bcc'].') / Lead : '.$job->getLead()->getId(). ' / To '.$data['email'].' / Sujet : ' . $sujet. ' / From : '.$from);
                    $message_client->addBcc($this->_formConfig['mails'][$form_subject]['bcc']);
                    $logger->info('Destinataire mail BCC ajouté dans le mail client');
                }

                // Envoi du mail au service client
                $data['demande-rdv'] = $this->subjects[$data['sujet']];
                $logger->info('[ComundiMail] - Envoi mail SERVICE client / Lead : '.$job->getLead()->getId(). ' / To '.$mail_service_client.' / Sujet : ' . $sujetAdv. ' / From : '.$from);
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
                    $logger->info('[ComundiMail] - Ajout BCC ('.$this->_formConfig['mails'][$form_subject]['bcc'].') SERVICE client / Lead : '.$job->getLead()->getId(). ' / To '.$mail_service_client.' / Sujet : ' . $sujetAdv. ' / From : '.$from);
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
                    $resutClient = $this->getContainer()->get('mailer')->send($message_client);
                    $logger->info('****** Envoi du mail client réussi ('.$job->getLead()->getId().')! ******');
                    $resultAdv = $this->getContainer()->get('mailer')->send($message_service_client);
                    $logger->info('****** Envoi du mail service client réussi ! ('.$job->getLead()->getId().') ******');
                } catch(\Exception $e) {
                    $hasError = true;
                    $logger->error("****** Erreur à l'envoi du mail de contact Comundi : ".$e->getMessage().' ******');
                }

                if($hasError) {
                    $status = $this->_exportUtils->getErrorStatus($job);
                    $msg = 'Erreur envoi de mail Comundi';
                } else {
                    $status = $exportUtils::$_EXPORT_SUCCESS;
                    $msg = 'Exporté avec succès';
                }

            } else {

                // Mode CRM Affectation
                $user_email = $this->_formConfig['mails'][$form_subject]['user_email'];
                $user = $this->getContainer()->get("doctrine")->getRepository('TellawLeadsFactoryBundle:Users')->findOneByEmail ( $user_email );

                if ( trim($user_email) == "" ) {
                    $hasError = true;
                    $status = $this->_exportUtils->getErrorStatus($job);
                    $msg = 'Probleme : Mode CRM, Email attribution vide';
                }

                if ( $user == null ) {
                    $hasError = true;
                    $status = $this->_exportUtils->getErrorStatus($job);
                    $msg = "Probleme : Mode CRM, Utilisateur non trouve pour l'email : ".$user_email;
                }

                // Affect lead to user
                $lead = $job->getLead();
                $lead->setUser( $user );
                $em = $this->getContainer()->get("doctrine")->getManager();
                $em->persist($lead);
                $em->flush();

                // Adding an entry to history
                $this->getContainer()->get("history.utils")->push ( "Attribution à : " . ucfirst($user->getFirstName()). " ". ucfirst($user->getLastName()), null, $lead );

                $prefUtils = $this->getContainer()->get('preferences_utils');
                $leadsUrl = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', $lead->getForm()->getScope()->getId());

                /**
                 * Send notification to a user
                 * Mail is sent to the user owner of the lead
                 */
                $result = $this->sendNotificationEmail ("Changement d'affectation pour la LEAD #".$job->getLead()->getId(),
                    "Un utilisateur vient de modifier l'affectation d'une lead.",
                    $user,
                    "Le ".date ("d/m/Y à h:i"). " la lead's factory vient de vous assigner la lead : ".$job->getLead()->getId()  ,
                    $leadsUrl,
                    $leadsUrl,
                    $lead->getForm()->getScope()->getId()
                );

                if ( !$result ) {
                    $hasError = true;
                    $status = $this->_exportUtils->getErrorStatus($job);
                    $msg = "Probleme : Mode CRM, L'email de notification n'a pas été envoyé : ".$user_email;
                }

                if(!$hasError) {
                    $status = $exportUtils::$_EXPORT_SUCCESS;
                    $msg = 'Exporté avec succès';
                }

            }

            $logger->info('Export Comundi mail fin : '.$job->getLead()->getId());
            $exportUtils->updateJob($job, $status, $msg);
            $exportUtils->updateLead($job->getLead(), $status, $msg);

        }
    }

    private function sendNotificationEmail ( $action, $detailAction, Users $user, $message, $urlLead, $urlApplication, $scopeId ) {

        $toEmail = $user->getEmail();
        $toName = ucfirst($user->getFirstname()) . ' ' . ucfirst($user->getLastname());

        $to = array($toEmail => $toName);

        $prefUtils = $this->getContainer()->get('preferences_utils');
        $from = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_EMAIL_SENDER', $scopeId);

        $subject = "Lead's Factory : ".$action;

        $template = $this->getContainer()->get('templating')->render(
            'TellawLeadsFactoryBundle::emails/lead_notification.html.twig',
            array(
                "action" => $action,
                "detailAction" => $detailAction,
                "user" => $user,
                "message" => $message,
                "urlLead" => $urlLead,
                "urlApplication" => $urlApplication,
            )
        );

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($template, 'text/html');

        return $this->getContainer()->get('mailer')->send($message);

    }
    
}
