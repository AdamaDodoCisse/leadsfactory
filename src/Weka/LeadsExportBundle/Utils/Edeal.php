<?php

namespace Weka\LeadsExportBundle\Utils;

use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;


class Edeal extends AbstractMethod{

	private $_credentials;
	
	/** @var  \Weka\LeadsExportBundle\Utils\Edeal\BaseMapping */
    private $_mappingClass;


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
        return null;
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

	    /** @var Export $job */
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

		    //on dégage si profil étudiant
		    if(isset($data['profil']) && $data['profil'] == 'ETUDIANT'){
			    $logger->info('Profil étudiant');
			    $exportUtils->updateJob($job, $exportUtils::$_EXPORT_NOT_SCHEDULED, 'Profil étudiant - pas d\'export');
			    $exportUtils->updateLead($job->getLead(), $exportUtils::$_EXPORT_NOT_SCHEDULED, 'Profil étudiant - pas d\'export');
			    continue;
		    }

            try {

                $enterprise = $this->_getEnterprise($data);
                var_dump($enterprise);
                $entResponse = $client->createEnterprise($enterprise);
                $logger->info('Edeal createEnterprise result : '.$entResponse);

                $person = $this->_getPerson($data);
                var_dump($person);
                $personResponse = $client->createPerson($person);
                $logger->info('Edeal createPerson result : '.$personResponse);

                $couponsWeb = $this->_getCouponsWeb($data);
                var_dump($couponsWeb);
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

            } catch (\Exception $e) {

                $status = $exportUtils->getErrorStatus($job);
                $log = $e->getMessage();

                $this->notifyOfExportIssue ( $e->getMessage(), $form, $job, $status );

                $exportUtils->updateJob($job, $status, $log);
                $exportUtils->updateLead($job->getLead(), $status, $log);
                $logger->error($log);

            }

        }
    }

	private function getMappedData($data, $mapping)
	{
		$entity = new \StdClass();
		foreach($mapping as $edealKey => $formKey) {

			$getter = 'get'.ucfirst(strtolower($edealKey));

			if (method_exists($this->_mappingClass, $getter)){
				$entity->$edealKey = $this->_mappingClass->$getter($data);
			}elseif(!empty($formKey)){
				$entity->$edealKey = isset($data[$formKey]) ? $data[$formKey] : null;
			}else{
				$entity->$edealKey = null;
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
     * @param $data
     * @return \StdClass
     */
    private function _getPerson($data)
    {
	    return $this->getMappedData($data, $this->_mappingClass->getPersonMapping());
    }

    /**
     * @param $data
     * @return \StdClass
     */
    private function _getEnterprise($data)
    {
	    $logger = $this->getContainer()->get('export.logger');
	    $logger->info('_getEnterprise');
	    return $this->getMappedData($data, $this->_mappingClass->getEnterpriseMapping());
    }

    /**
     * Retrieve mapping class
     * Basé sur la clé mapping_class de la config du formulaire
     * Si la clé mapping_class n'est pas renseignée, cherche une classe de mapping basée sur le nom de code du formulaire
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

	    if(isset($config['export']['edeal']['mapping_class'])){
		    $logger->info($config['export']['edeal']['mapping_class']);
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Edeal\\" . $scopePath . $config['export']['edeal']['mapping_class'];
	    }else{
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Edeal\\" . $scopePath . ucfirst($form->getCode());
	    }
		$em = $this->getContainer()->get('doctrine')->getManager();
        $list_element_repository = $this->getContainer()->get('leadsfactory.reference_list_element_repository');
        return (class_exists($className)) ? new $className($em, $list_element_repository) : null;
    }

	/**
	 * Gestion des cas particuliers des exports avec e-mails non validés
	 *
	 * @param $lead
	 * @param $email
	 *
	 * @return bool
	 */
	public function isEmailValidated($lead, $email)
	{
		$form = $lead->getForm()->getCode();
		$data = json_decode($lead->getData(), true);

		if(in_array($data['pays'], array('FR', 'BE', 'LU', 'CH', 'MC')) && $form == 'ti_extrait'){
			return true;
		}else{
			return parent::isEmailValidated($lead, $email);
		}
	}
}
