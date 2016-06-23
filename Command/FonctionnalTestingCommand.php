<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tellaw\LeadsFactoryBundle\Entity\Field;
use Tellaw\LeadsFactoryBundle\TellawLeadsFactoryBundle;

class FonctionnalTestingCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('leadsfactory:testing:run')
            ->setDescription('Command running functional testing of forms.')
            ->addArgument('form', InputArgument::OPTIONAL, 'form code')
            ->addOption('fields', null, InputOption::VALUE_NONE, 'If set, fields references will be updated')
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
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {


        $alertUtils = $this->getContainer()->get("alertes_utils");
        $formUtils = $this->getContainer()->get("form_utils");
        $testUtils = $this->getContainer()->get("functionnal_testing.utils");
        $fields_update = $input->getOption('fields');
        $fields_list = array();

        $testUtils->setOutputInterface ( $output );

        $form = $input->getArgument('form');
        if ($form) {
            $output->writeln('Testing ONLY ' . $form . '...');
            $forms = $this->getContainer()->get('leadsfactory.form_repository')->findByCode($form);
        } else {
            $output->writeln('Testing every forms...');
            $forms = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        }

        foreach ( $forms as $form ) {

            $output->writeln ("Traitement formulaire : ".$form->getName());

            $form_config = $form->getConfig();

            // If updating fields
            if ($fields_update) {
                $this->get_reference_fields($form->getSource(), $fields_list);
                continue;
            }

            // If functional test purpose
            if (isset( $form_config ["configuration"]["functionnalTestingEnabled"] ) && $form_config ["configuration"]["functionnalTestingEnabled"] == true) {
                $output->writeln ("Traitement de la page de test : ".$form->getUrl());
                $testUtils->run ( $form );
            } else {
                $output->writeln ("Le formulaire n'est pas configuré pour réaliser les tests fonctionnels");
            }
        }

        if ($fields_list) {
            $fields_list = array_values(array_unique($fields_list));
            sort($fields_list);
            print_r($fields_list);
            $this->update_reference_fields($fields_list);
        }
    }


    protected function update_reference_fields($fields) {

        $fieldsRepository = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Field');
        $em = $this->getContainer()->get("doctrine")->getEntityManager();
        foreach ( $fields as $field) {
            $search = $fieldsRepository->findByCode($field);
            // No match
            if (!$search) {
                $new_field = new Field();
                $new_field->setCode($field);
                $em->persist($new_field);
                $em->flush();
            }
        }
        $query = $em->createQuery("SELECT f FROM TellawLeadsFactoryBundle:Field f");
        $res = $query->getResult();
        print_r($res);
    }

    protected function get_reference_fields($form_source, &$data_fields) {
        $clean_source = strip_tags($form_source);
        $fields = array();
        $ret = preg_match_all("({{(.*)}})", $clean_source, $fields);

        if ($ret) {
            foreach ($fields[1] as $field) {
                $json_field = array();
                $ret2 = preg_match_all("(field\\((.*)\\))", $field, $json_field);
                if ($ret2) {
                    $json_string = str_replace("'", '"', $json_field[1][0]);
                    $json_data = json_decode($json_string, true);
                    if (isset($json_data["attributes"])) {
                        $attributes = $json_data["attributes"];
                        if (isset($attributes["id"])) {
                            $data_fields[] = $attributes["id"];
                        }
                    }
                }
            }
        }
    }
}