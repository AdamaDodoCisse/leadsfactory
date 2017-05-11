<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends ContainerAwareCommand
{

    private static $_PID_FILE = "export_command_is_running.lock";

    protected function configure()
    {
        $this->setName('leadsfactory:export:leads')
            ->setDescription('Export leads')
            ->addArgument('form', InputArgument::OPTIONAL, 'form code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $logger = $this->getContainer()->get('export.logger');

        if (!$this->isExportRunning()) {

            $this->createExportPid();
            $output->writeln('Info : Created LOCK file : ' . ExportCommand::$_PID_FILE);

            $form = $input->getArgument('form');
            if ($form) {
                $output->writeln('Exporting ' . $form . ' leads...');
                $forms = $this->getContainer()->get('leadsfactory.form_repository')->findByCode($form);
            } else {
                $output->writeln('Exporting all leads...');
                $forms = $this->getContainer()->get('leadsfactory.form_repository')->findAll();
            }

            foreach ($forms as $form) {
                $output->writeln($form->getName());
                try {
                    $this->getContainer()->get('export_utils')->export($form);
                    $output->writeln('Done');
                } catch (\Exception $e) {
                    $output->writeln('Error : ' . $e->getMessage());
                    $output->writeln('Error : ' . $e->getTraceAsString());
                    $logger->error('Export ' . $form->getName() . ' error : ' . $e->getMessage());
                    $logger->error($e->getTraceAsString());
                }
            }

            $this->removePidFile();
            $output->writeln('Info : Removed LOCK file : ' . ExportCommand::$_PID_FILE);

        } else {
            $output->writeln('Un export est déjà en traitement. Si c\'est anormal, merci de détruire le fichier LOCK :' . ExportCommand::$_PID_FILE);
            $logger->error('Un export est déjà en traitement. Si c\'est anormal, merci de détruire le fichier LOCK :' . ExportCommand::$_PID_FILE);
        }
    }

    private function createExportPid()
    {

        $somecontent = date("c");

        if (!$handle = fopen(ExportCommand::$_PID_FILE, 'a')) {
            throw new \Exception ("Impossible d'ouvrir le fichier (" . ExportCommand::$_PID_FILE . ")");
        }

        if (fwrite($handle, $somecontent) === FALSE) {
            throw new \Exception ("Impossible d'écrire dans le fichier (" . ExportCommand::$_PID_FILE . ")");
        }

        fclose($handle);

    }

    private function isExportRunning()
    {
        if (file_exists(ExportCommand::$_PID_FILE)) {
            return true;
        } else {
            return false;
        }
    }

    private function removePidFile()
    {
        unlink(ExportCommand::$_PID_FILE);
        if (file_exists(ExportCommand::$_PID_FILE)) {
            throw new \Exception ("Impossible de retirer le fichier LOCK (" . ExportCommand::$_PID_FILE . ")");
        }
    }

}
