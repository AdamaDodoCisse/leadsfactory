<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 27/10/15
 * Time: 16:55
 */

namespace LeadsFactoryBundle\Command;

use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Monolog\Logger;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Exception\Exception;


class DebugCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('leadsfactory:debug')
            ->setDescription('Test mail sending for debug');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $logger = $this->getContainer()->get('export.logger');

        echo("Send a test mail");

        $templatingService = $this->getContainer()->get('templating');

        $testMail = \Swift_Message::newInstance()
            ->setSubject("Contact – question inscription en cours")
            ->setFrom("inscriptions@comundi.fr")
            ->setTo("ewallet@weka.fr")
            // HTML version
            ->setBody(
                $templatingService->render(
                    'WekaLeadsExportBundle:Emails:Comundi/contact.html.twig',
                    array(
                        'content' => "test de contenu",
                        'mail_contact' => "eric.wallet@yahoo.fr",
                        'tel' => "0681428259",
                        'user_data' => ['firstName' => 'firstName',
                            'lastName' => 'LastName',
                        ],
                    )
                ),
                'text/html'
            )
            // Plaintext version
            ->addPart(
                $templatingService->render(
                    'WekaLeadsExportBundle:Emails:Comundi/contact.txt.twig',
                    array(
                        'content' => "test de contenu",
                        'mail_contact' => "eric.wallet@yahoo.fr",
                        'tel' => "0681428259",
                        'user_data' => ['firstName' => 'firstName',
                            'lastName' => 'LastName',
                        ],
                    )
                ),
                'text/plain'
            );

        $testMail->addBcc("eric.wallet@yahoo.fr");

        try {
            $this->getContainer()->get('mailer')->send($testMail);
            $logger->info('****** Envoi du mail TEST réussi ! ******');
        } catch (\Exception $e) {
            echo("Erreur !!!! " . $e - getMessage());
        }
    }

}
