<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\Scope;

/**
 * Prends des emails et les transforme en leads
 */
class EmailToLeadsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('leadsfactory:extract:emails')
            ->setDescription('Export leads Venant des emails')
            ->addArgument('scope', InputArgument::REQUIRED, 'Scope dans lequel appliquer les exports');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $scopeName = $input->getArgument('scope');

        /**
         * @var Scope
         */
        $scope = $this->getContainer()->get('leadsfactory.scope_repository')->findOneByName($scopeName);

        /**
         * @var Preference
         */
        $email = $this->getContainer()->get('leadsfactory.preference_repository')->findByKeyAndScope(
            'EMAIL_TO_LEADS_EMAIL',
            $scope->getId()
        );
        $password = $this->getContainer()->get('leadsfactory.preference_repository')->findByKeyAndScope(
            'EMAIL_TO_LEADS_PASSWORD',
            $scope->getId()
        );

        $inbox = \imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $email->getValue(), $password->getValue());

        if (!$inbox) {
            echo imap_last_error();
            return false;
        }

        $emails = imap_search($inbox, 'UNSEEN', SE_FREE, "UTF-8");
        if (!$emails) {
            return false;
        }
        $output->writeln(count($emails)." email(s) a traiter ...");
        /* for every email... */
        foreach ($emails as $id => $email_number) {

            $data = array();
            /* get information specific to this email */
            $header = imap_headerinfo($inbox, $email_number);
            $message = imap_fetchbody($inbox, $email_number, FT_PEEK);


            if (@$header->from[0]->personal) {
                $personal = explode(' ', $header->from[0]->personal);
                $data['firstName'] = @$personal[0];
                $data['lastName'] = @$personal[1];
            } else {
                $data['firstName'] = '-';
                $data['lastName'] = '-';
            }
            $data['email'] = $header->from[0]->mailbox.'@'.$header->from[0]->host;
            $messageClean = trim(strip_tags($message));

            $data['comment'] = $messageClean;

            // Créer la lead
            $this->createLead($data, "comundi_email_extract_form");
            // Mettre a jour la lecture
            imap_setflag_full($inbox, $email_number, "\\Seen", ST_UID);
            $output->writeln("MAIL Traité : ".$data['email']." >> ".$header->subject);
        }

        // close the connection
        imap_expunge($inbox);
        imap_close($inbox);

        return true;

    }


    /**
     * Creation d'une lead
     * @param $data
     * @param $formCode
     * @return bool
     */
    protected function createLead($data, $formCode)
    {
        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getContainer()->get('logger');
        $searchUtils = $this->getContainer()->get('search.utils');

        $logger->info('API post lead');

        try {
            /**
             * @var Form
             */
            $form = $this->getContainer()->get('doctrine')->getRepository(
                'TellawLeadsFactoryBundle:Form'
            )->findOneByCode($formCode);

            // Get the Json configuration of the form
            $config = $form->getConfig();

            $data = $this->getContainer()->get('form_utils')->preProcessData($form->getId(), $data);
            $jsonContent = json_encode($data);

            $leads = new Leads();
            $leads->setIpadress('localhost');
            $leads->setUserAgent('EXPORTED VIA EMAIL COMMAND');
            $leads->setFirstname(@$data['firstName']);
            $leads->setLastname(@$data['lastName']);
            $leads->setData($jsonContent);
            $leads->setLog("leads importée le : ".date('Y-m-d h:s'));
            $leads->setForm($form);
            $leads->setEmail(@$data['email']);

            // Assignation de la leads si la configuration est presente
            if (isset($config['configuration'])) {

                if (array_key_exists('assign', $config["configuration"])) {

                    $assign = trim($config["configuration"]["assign"]);
                    $user = $this->getContainer()->get('doctrine')->getRepository(
                        'TellawLeadsFactoryBundle:Users'
                    )->findOneByEmail($assign);

                    if ($user != null) {
                        $leads->setUser($user);
                    } else {
                        $logger->info("Frontcontroller : Assign to a User that does not exists! ".$assign);
                    }

                }

                if (array_key_exists('status', $config["configuration"])) {
                    $status = trim($config["configuration"]["status"]);
                    $leads->setWorkflowStatus($status);
                }

                if (array_key_exists('type', $config["configuration"])) {
                    $type = trim($config["configuration"]["type"]);
                    $leads->setWorkflowType($type);
                }

                if (array_key_exists('theme', $config["configuration"])) {
                    $theme = trim($config["configuration"]["theme"]);
                    $leads->setWorkflowTheme($theme);
                }

            }

            $status = $exportUtils->hasScheduledExport(
                $form->getConfig()
            ) ? $exportUtils::$_EXPORT_NOT_PROCESSED : $exportUtils::$_EXPORT_NOT_SCHEDULED;
            $leads->setStatus($status);

            $leads->setCreatedAt(new \DateTime());

            /**
             * @var EntityManager
             */
            $em = $this->getContainer()->get('doctrine')->getEntityManager();
            $em->persist($leads);
            $em->flush();

            // Index leads on search engine
            $leads_array = $this->getContainer()->get('leadsfactory.leads_repository')->getLeadsArrayById(
                $leads->getId()
            );
            $searchUtils->indexLeadObject($leads_array, $leads->getForm()->getScope()->getCode());

            // Create export job(s)
            if ($status == $exportUtils::$_EXPORT_NOT_PROCESSED) {
                $exportUtils->createJob($leads);
            }

            if (isset($config["configuration"])
                && array_key_exists('enableApiNotificationEmail', $config["configuration"])
                && $config["configuration"]["enableApiNotificationEmail"] == true
            ) {
                //Send notification
                if (isset($config['notification'])) {
                    $logger->info("API : Envoi de notifications");
                } else {
                    $logger->info("API : Le bloc de configuration de Notification n'existe pas en config");
                }
            } else {
                $logger->info("API : Le formulaire refuse l'envoi de mail par notification");
            }


            return true;

        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return false;
        }
    }
}
