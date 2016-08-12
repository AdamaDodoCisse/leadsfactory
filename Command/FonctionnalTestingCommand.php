<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\Field;

class FonctionnalTestingCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('leadsfactory:testing:run')
            ->setDescription('Command running functional testing of forms.')
            ->addArgument('form', InputArgument::OPTIONAL, 'form code')
            ->addOption('fields', null, InputOption::VALUE_NONE, 'If set, fields references will be updated');
    }

    /**
     *
     * execute
     *
     * Method used to set yesterdays status history in database.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $alertUtils = $this->getContainer()->get("alertes_utils");
        $formUtils = $this->getContainer()->get("form_utils");
        $testUtils = $this->getContainer()->get("functionnal_testing.utils");
        $fields_update = $input->getOption('fields');
        $fields_list = array();

        $testUtils->setOutputInterface($output);

        $form = $input->getArgument('form');
        if ($form) {
            $output->writeln('Testing ONLY ' . $form . '...');
            $forms = $this->getContainer()->get('leadsfactory.form_repository')->findByCode($form);
        } else {
            $output->writeln('Testing every forms...');
            $forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        }

        foreach ($forms as $form) {

            // If updating fields
            if ($fields_update) {
                $fields_list[] = $formUtils->getFieldsAsArray($form->getSource());
                continue;
            }

            $output->writeln("Traitement formulaire : " . $form->getName());
            $form_config = $form->getConfig();

            // If functional test purpose
            if (isset($form_config ["configuration"]["functionnalTestingEnabled"]) && $form_config ["configuration"]["functionnalTestingEnabled"] == true) {
                $output->writeln("Traitement de la page de test : " . $form->getUrl());
                $testUtils->run($form);
            } else {
                $output->writeln("Le formulaire n'est pas configuré pour réaliser les tests fonctionnels");
            }
        }

        if ($fields_list) {
            $output->writeln("Mise à jour des champs du reférentiel ...");
            $count = $this->update_reference_fields($fields_list);
            $output->writeln("Nombre de nouveaux champs : $count");
        }

    }


    protected function getScopesArray()
    {
        $scopeList = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Scope')->getAll();
        $arr_scope = array();
        $arr_scope["default"] = "";
        if ($scopeList)
            foreach ($scopeList as $scope)
                $arr_scope[$scope['s_code']] = "";

        return $arr_scope;
    }

    protected function mergeFields($fieldsList)
    {
        $fields_list = array_filter($fieldsList);
        $merged_list = array();
        foreach ($fields_list as $fields) {
            foreach ($fields as $key => $field) {
                if (!isset($merged_list[$key]))
                    $merged_list[$key] = $field['attributes'];
            }
        }

        return $merged_list;
    }

    protected function update_reference_fields($fields)
    {
        $count = 0;
        $fieldsRepository = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Field');
        $arr_scope = $this->getScopesArray();
        $em = $this->getContainer()->get("doctrine")->getEntityManager();
        $fields = $this->mergeFields($fields);
        foreach ($fields as $code => $field) {
            $testValues = $arr_scope;
            $testValues['default'] = isset($field['test-value']) ? $field['test-value'] : "";
            $field = $fieldsRepository->findOneByCode($code);
            // No match
            if (!$field) {
                $new_field = new Field();
                $new_field->setCode($code);
                $new_field->setTestValue(json_encode($testValues));
                $em->persist($new_field);
                $count++;
            } else {
                // first check if data is already set
                // If is is already check and not empty form form source replace (default)
                $testValuesArr = json_decode($field->getTestValue(), true);
                if (is_array($testValuesArr)) { // data is valid JSON
                    foreach ($testValuesArr as $scope => $item)
                        if ($testValuesArr[$scope] != $testValues[$scope] && !empty($testValues[$scope]))
                            $testValuesArr[$scope] = $testValues[$scope];
                    $field->setTestValue(json_encode($testValuesArr));
                } else { // data is not valid JSON
                    $field->setTestValue(json_encode($testValues));
                }
                $em->persist($field);

            }
        }

        $em->flush();

        return $count;
    }

}
