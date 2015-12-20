<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FonctionnalTestingCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('leadsfactory:testing:run')
            ->setDescription('Command running foncitonnal testing of forms.')
        ;
    }

    /**
     *
     * execute
     *
     * Method used to set yesterdays status history in database.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        $alertUtils = $this->getContainer()->get("alertes_utils");
        $formUtils = $this->getContainer()->get("form_utils");
        $testUtils = $this->getContainer()->get("functionnal_testing.utils");

        $testUtils->setOutputInterface ( $output );

        foreach ( $forms as $form ) {

            $output->writeln ("Traitement formulaire : ".$form->getName());

            $formId = $form->getConfig();
            if (isset( $formId ["configuration"]["functionnalTestingEnabled"] ) && $formId ["configuration"]["functionnalTestingEnabled"] == true) {

                $output->writeln ("Traitement de la page de test : ".$form->getUrl());
                $testUtils->run ( $form );

            } else {
                $output->writeln ("Le formulaire n'est pas configuré pour réaliser les tests fonctionnels");
            }
        }
    }
}