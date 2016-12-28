<?php

namespace Weka\LeadsExportBundle\Utils;

use Citrix\Authentication\Direct;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;


class Gotowebinar extends AbstractMethod
{

    /** @var  \Weka\LeadsExportBundle\Utils\Gotowebinar\BaseMapping */
    private $_mappingClass;

    private $_credentials;

    public function __construct($credentials)
    {
        $this->_credentials = $credentials;
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

        $logger->info('GotoWebinar export start '.$form->getName());

        $this->_mappingClass = $this->_getMapping($form);
        $scope = $form->getScope()->getCode();

        if (is_null($this->_mappingClass)) {
            $error = 'Mapping inexistant FORM '.$form->getCode();
            $logger->error($error);
        }

        //try {
        $client = new Direct($this->_credentials[$scope]['consumer_key']);
        $client->auth($this->_credentials[$scope]['user'], $this->_credentials[$scope]['password']);
        $goToWebinar = new \Citrix\GoToWebinar($client);
        //}catch(\Exception $e){
        //    $logger->error($e->getMessage());
        //}

        /** @var Export $job */
        foreach ($jobs as $job) {

            try {
                if (!empty($error)) {
                    $job->setLog($error);
                    $job->setStatus($exportUtils->getErrorStatus($job));

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $em->persist($job);
                    $em->flush();

                    continue;
                }

                $logger->info('job ID : '.$job->getId());

                $data = json_decode($job->getLead()->getData(), true);
                $webinarKey = $data['gotowebinar_key'];

                $registrantData = $this->getMappedData($data, $this->_mappingClass->getMapping());
                $registration = $goToWebinar->register($webinarKey, $registrantData);

                // Si il y a une erreur
                if (!isset($registration['errorCode'])) {
                    $log = "Exporté avec succès";
                    $status = $exportUtils::$_EXPORT_SUCCESS;
                } else {
                    $log = $registration['description'];
                    $status = $exportUtils->getErrorStatus($job);
                }

                $exportUtils->updateJob($job, $status, $log);
                $exportUtils->updateLead($job->getLead(), $status, $log);
                $logger->info($log);

            } catch (\Exception $e) {

                $status = $exportUtils->getErrorStatus($job);
                $log = $e->getMessage();

                $this->notifyOfExportIssue($e->getMessage(), $form, $job, $status);

                $exportUtils->updateJob($job, $status, $log);
                $exportUtils->updateLead($job->getLead(), $status, $log);
                $logger->error($log);

            }


        }
    }

    private function getMappedData($data, $mapping)
    {
        $entity = array();
        foreach ($mapping as $gtwKey => $formKey) {

            $getter = 'get'.ucfirst(strtolower($gtwKey));

            if (method_exists($this->_mappingClass, $getter)) {
                $entity[$gtwKey] = $this->_mappingClass->$getter($data);
            } elseif (!empty($formKey)) {
                $entity[$gtwKey] = isset($data[$formKey]) ? $data[$formKey] : null;
            } else {
                $entity[$gtwKey] = null;
            }
        }

        return $entity;
    }

    /**
     * @param $data
     * @return \StdClass
     *
     */
    private function _getCouponsWeb($data)
    {
        return $this->getMappedData($data, $this->_mappingClass->getCouponsWebMapping());
    }

    /**
     * get Mapping class
     *
     * @param Form $form
     * @return mixed
     */
    private function _getMapping($form)
    {
        $logger = $this->getContainer()->get('export.logger');

        $config = $form->getConfig();
        $scope = !is_null($form->getScope()) ? $form->getScope()->getCode() : null;
        $scopePath = !is_null($scope) ? $scope."\\" : '';

        if (isset($config['export']['gotowebinar']['mapping_class'])) {
            $logger->info($config['export']['gotowebinar']['mapping_class']);
            $className = "\\Weka\\LeadsExportBundle\\Utils\\Gotowebinar\\".$scopePath.$config['export']['gotowebinar']['mapping_class'];
        }
        $em = $this->getContainer()->get('doctrine')->getManager();
        $list_element_repository = $this->getContainer()->get('leadsfactory.reference_list_element_repository');

        return (class_exists($className)) ? new $className($em, $list_element_repository) : null;
    }
}
