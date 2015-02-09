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

	private $_credentials;

    private $_mappingClass;


	public function __construct($credentials)
    {
        $this->_credentials = $credentials;
		$x=0;
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

	    $scope = $form->getScope()->getCode();

        $logger->info('Edeal export start '.$form->getName());
	    $logger->info('wsdl : '.$this->_credentials[$scope]['wsdl']);
	    $logger->info('user : '.$this->_credentials[$scope]['user']);

        $client  = new \SoapClient($this->_credentials[$scope]['wsdl'], array('soap_version' => SOAP_1_2, 'trace' => true));
        $response = $client->authenticate($this->_credentials[$scope]['user'], $this->_credentials[$scope]['password']);

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

	        $logger->info('job ID : '.$job->getId());

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

	        $getter = 'get'.ucfirst(strtolower($edealKey));

	        if (method_exists($this->_mappingClass, $getter)){
		        $couponsWeb->$edealKey = $this->_mappingClass->$getter($data);
	        }elseif(!empty($formKey)){
		        $couponsWeb->$edealKey = isset($data[$formKey]) ? $data[$formKey] : null;
	        }else{
		        $couponsWeb->$edealKey = null;
	        }

            /*if(empty($formKey)){
                $getter = 'get'.ucfirst(strtolower($edealKey));
                if (method_exists($this->_mappingClass, $getter)){
                    $couponsWeb->$edealKey = $this->_mappingClass->$getter($data);
                }else{
                    $couponsWeb->$edealKey = null;
                }
            }else{
                $couponsWeb->$edealKey = isset($data[$formKey]) ? $data[$formKey] : null;
            }*/
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

	        $getter = 'get'.ucfirst(strtolower($edealKey));

	        if (method_exists($this->_mappingClass, $getter)){
		        $person->$edealKey = $this->_mappingClass->$getter($data);
	        }elseif(!empty($formKey)){
		        $person->$edealKey = isset($data[$formKey]) ? $data[$formKey] : null;
	        }else{
		        $person->$edealKey = null;
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
	    $logger = $this->getContainer()->get('export.logger');

        $enterprise = new \StdClass();
        foreach($this->_mappingClass->getEnterpriseMapping() as $edealKey => $formKey){

	        $getter = 'get'.ucfirst(strtolower($edealKey));

	        if (method_exists($this->_mappingClass, $getter)){
		        $enterprise->$edealKey = $this->_mappingClass->$getter($data);
	        }elseif(!empty($formKey)){
		        $enterprise->$edealKey = isset($data[$formKey]) ? $data[$formKey] : null;
	        }else{
		        $enterprise->$edealKey = null;
	        }


	        /*if(empty($formKey)){
                $getter = 'get'.ucfirst(strtolower($edealKey));
                if (method_exists($this->_mappingClass, $getter)){
                    $enterprise->$edealKey = $this->_mappingClass->$getter($data);
	                $logger->info($enterprise->$edealKey);
                }else{
                    $enterprise->$edealKey = null;
                }
            }else{
                $enterprise->$edealKey = isset($data[$formKey]) ? $data[$formKey] : null;
	            $logger->info($enterprise->$edealKey);
            }*/
        }



        return $enterprise;
    }

    /**
     * Retrieve mapping class
     * Basé sur la clé mapping_class de la config du formulaire
     * Si la clé mapping_class n'est pas renseignée, cherche une classe de mapping basée sur le nom de code du formulaire
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @return mixed
     */
    private function _getMapping($form)
    {
	    $logger = $this->getContainer()->get('export.logger');

	    $config = $form->getConfig();
	    $scope = !is_null($form->getScope()) ? $form->getScope()->getCode() : null;
	    $scopePath = !is_null($scope) ? $scope."\\" : '';

	    if(isset($config['export']['edeal']['mapping_class'])){
		    $logger->info($config['export']['edeal']['mapping_class']);
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Edeal\\" . $scopePath . $config['export']['edeal']['mapping_class'];
		    $logger->info($className);
	    }else{
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Edeal\\" . $scopePath . ucfirst($form->getCode());
	    }
		$em = $this->getContainer()->get('doctrine')->getManager();
        return (class_exists($className)) ? new $className($em) : null;
    }


}