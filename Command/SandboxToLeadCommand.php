<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 27/10/15
 * Time: 16:55
 */

namespace Tellaw\LeadsFactoryBundle\Command;

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


class SandboxToLeadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('leadsfactory:sandbox:process')
            ->setDescription('Jobs used to process Sandbox and transfer to real leads');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('export.logger');
        $repository = $this->getContainer()->get('leadsfactory.sandbox_repository');
        $prefUtils = $this->getContainer()->get('preferences_utils');

        $sandboxItems = $repository->findSandboxOutdated();
        $output->writeln('Number of sandox items to process : '.count($sandboxItems));
        // Foreach Sandbox item, create a lead and delete sandbox element
        foreach ( $sandboxItems as $item ) {

            $output->writeln('Processing Item : '.$item->getId());

            // Find Associated Form and Scope
            $form = $this->getContainer()->get("doctrine")->getRepository('TellawLeadsFactoryBundle:Form')->findOneByCode($item->getFormCode());

            if ($form) {

                $scope = $form->getScope();
                if (!$scope) {
                    $urlDb = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL');
                } else {
                    $urlDb = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', $scope->getId());
                }

                // Get Url based on configuration
                if (trim($urlDb) != "") {
                    if (strstr($urlDb, "web/")) {
                        $url = $urlDb . "api/lead/post";
                    } else {
                        $url = $urlDb . "web/api/lead/post";
                    }
                }

                $output->writeln('Leads URL for Item : '.$url);

                // Post Data
                $ci = curl_init();
                curl_setopt($ci, CURLOPT_URL, $url);
                curl_setopt($ci, CURLOPT_PORT, '80');
                curl_setopt($ci, CURLOPT_TIMEOUT, 10);
                curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ci, CURLOPT_POSTFIELDS, $item->getData());

                curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ci);
                $error = curl_error($ci);
                curl_close($ci);

                $output->writeln('Response is : '.$result);

                // If ok, then delete Sandbox item
                if ($result != "") {
                    // Ok
                    $em = $this->getContainer()->get("doctrine")->getEntityManager();
                    $em->remove($item);
                    $em->flush();
                } else {
                    // Erreur
                    throw new Exception ("API Feedback is not correct, unable to process");
                }
            } else {
                $output->writeln('Form code in sandbox is not valid : '.$item->getFormCode());
            }
            $output->writeln('end of process for item : '.$item->getId());

        }

    }

}
