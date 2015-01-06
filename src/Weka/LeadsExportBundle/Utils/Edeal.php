<?php

namespace Weka\LeadsExportBundle\Utils;

use Symfony\Component\Security\Acl\Exception\Exception;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;


class Edeal extends AbstractMethod{

    private $_wsdl;
    private $_user;
    private $_password;

    private $_mappingClass;

    public function __construct($wsdl, $user, $password)
    {
        $this->_wsdl = $wsdl;
        $this->_user = $user;
        $this->_password = $password;
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

        $logger->info('Edeal export start');

        $client  = new \SoapClient($this->_wsdl, array('soap_version' => SOAP_1_2, 'trace' => true));
        $response = $client->authenticate($this->_user, $this->_password);

        if(!$response){
            $error = 'Edeal : l\'authentification a échouée FORM '.$form->getCode();
            $logger->error($error);
        }

        $this->_mappingClass = $this->_getMapping($form);

        if(is_null($this->_mappingClass)){
            $error = 'Mapping inexistant FORM '.$form->getCode();
            $logger->error($error);
        }

        foreach($jobs as $job){

            if(!empty($error)){
                $job->setLog($error);
                $job->setStatus($exportUtils->getErrorStatus($job));

                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($job);
                $em->flush();

                continue;
            }

            $data = json_decode($job->getLead()->getData(), true);

            $enterprise = $this->_getEnterprise($data);
            $entResponse = $client->createEnterprise($enterprise);
            $logger->info('Edeal createEnterprise result : '.$entResponse);

            $person = $this->_getPerson($data);
            $personResponse = $client->createPerson($person);
            $logger->info('Edeal createPerson result : '.$personResponse);

            $couponsWeb = $this->_getCouponsWeb($data);
            $cpwResponse = $client->createCouponsWeb_($couponsWeb);
            $logger->info('Edeal createCouponsWeb_ result : '.$cpwResponse);

            if($entResponse && $personResponse && $cpwResponse){
                $log = "Exporté avec succès";
                $status = $exportUtils::$_EXPORT_SUCCESS;
            }else{
                $log = 'Export échoué';
                $status = $exportUtils->getErrorStatus($job);
            }

            $exportUtils->updateJob($job, $status, $log);
            $exportUtils->updateLead($job->getLead(), $status, $log);
            $logger->info($log);
        }
    }

    /**
     * @param $data
     * @return \StdClass
     *
     */
    private function _getCouponsWeb($data)
    {
        $couponsWeb = new \StdClass();
        foreach($this->_mappingClass->getCouponsWebMapping() as $edealKey => $formKey){
            if(empty($formKey)){
                $getter = 'get'.ucfirst(strtolower($edealKey));
                if (method_exists($this->_mappingClass, $getter)){
                    $couponsWeb->$edealKey = $this->_mappingClass->$getter($data);
                }else{
                    $couponsWeb->$edealKey = null;
                }
            }else{
                $couponsWeb->$edealKey = $data[$formKey];
            }
        }

        return $couponsWeb;
    }

    /**
     * @param $data
     * @return \StdClass
     */
    private function _getPerson($data)
    {
        $person = new \StdClass();
        foreach($this->_mappingClass->getPersonMapping() as $edealKey => $formKey){
            if(empty($formKey)){
                $getter = 'get'.ucfirst(strtolower($edealKey));
                if (method_exists($this->_mappingClass, $getter)){
                    $person->$edealKey = $this->_mappingClass->$getter($data);
                }else{
                    $person->$edealKey = null;
                }
            }else{
                $person->$edealKey = $data[$formKey];
            }
        }

        return $person;
    }

    /**
     * @param $data
     * @return \StdClass
     */
    private function _getEnterprise($data)
    {
        $enterprise = new \StdClass();
        foreach($this->_mappingClass->getEnterpriseMapping() as $edealKey => $formKey){
            if(empty($formKey)){
                $getter = 'get'.ucfirst(strtolower($edealKey));
                if (method_exists($this->_mappingClass, $getter)){
                    $enterprise->$edealKey = $this->_mappingClass->$getter($data);
                }else{
                    $enterprise->$edealKey = null;
                }
            }else{
                $enterprise->$edealKey = $data[$formKey];
            }
        }

        return $enterprise;
    }

    /**
     * Retrieve mapping class
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @return mixed
     */
    private function _getMapping($form)
    {
	    $config = $form->getConfig();

	    $logger = $this->getContainer()->get('export.logger');

	    if(isset($config['export']['edeal']['mapping_class'])){
		    $logger->info($config['export']['edeal']['mapping_class']);
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Edeal\\" . $config['export']['edeal']['mapping_class'];
	    }else{
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Edeal\\" . ucfirst($form->getCode());
	    }

        return (class_exists($className)) ? new $className : null;
    }


}