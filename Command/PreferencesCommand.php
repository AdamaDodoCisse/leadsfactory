<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Cron\CronExpression;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Utils\PreferencesUtils;

class PreferencesCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
            ->setName('leadsfactory:preferences')
            ->setDescription('Check the status of preferences on this setup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln(" ");
        $output->writeln("This program lists preferences declared by any module of the application to the preference service using the PreferencesUtils::registerKey method in a non static mode");
        $output->writeln("Please refer to the documentation to add your preferences. Any preference to be useable in the application has to delcare the using this method!");


        $preferencesUtils = $this->getContainer()->get('preferences_utils');
        $preferences = $preferencesUtils->getListOfRequiredPreferences();

        $table = $this->getHelper('table');
        $table->setHeaders(array('Key', 'Description', 'Priorité', 'Scope'));

        $output->writeln(" ");
        $output->writeln("*********************");
        $output->writeln("Required Preferences");
        $rows = array();
        foreach ($preferences as $key => $attributes) {

            if ($attributes["scope"]) {
                $scope = "Scopes specific configuration Required";
            } else {
                $scope = "Global only Required";
            }

            if ($attributes["priority"] == PreferencesUtils::$_PRIORITY_REQUIRED) {
                $priority = "Required";
            } else {
                $priority = "OPtionnal";
            }

            $rows[] = array($key, $attributes["description"], $priority, $scope);

            $preferencesUtils->getValuesForKey($key);

        }

        $table->setRows($rows);
        $table->render($output);

        $table = $this->getHelper('table');
        $table->setHeaders(array('Key', 'Description', 'Priorité', 'Scope'));

        $output->writeln(" ");
        $output->writeln("*********************");
        $output->writeln("Optionnal Preferences");
        $preferences = $preferencesUtils->getListOfOptionnalPreferences();
        $rows = array();
        foreach ($preferences as $key => $attributes) {

            if ($attributes["scope"]) {
                $scope = "Scopes specific configuration Required";
            } else {
                $scope = "Global only Required";
            }

            if ($attributes["priority"] == PreferencesUtils::$_PRIORITY_REQUIRED) {
                $priority = "Required";
            } else {
                $priority = "Optionnal";
            }

            $rows[] = array($key, $attributes["description"], $priority, $scope);
            $values = $preferencesUtils->getValuesForKey($key);
        }

        $table->setRows($rows);
        $table->render($output);


    }


}
