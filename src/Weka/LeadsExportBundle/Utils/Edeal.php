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

        $client  = new \SoapClient($this->_wsdl, array('soap_version' => SOAP_1_2, 'trace' => true));
        $response = $client->authenticate($this->_user, $this->_password);

        if(!$response){
            $error = 'Edeal : l\'authentification a échouée FORM '.$form->getCode();
            $logger->error($error);
        }

        $mappingClass = $this->_getMapping($form);

        if(is_null($mappingClass)){
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

            $enterprise = new \StdClass();
            foreach($mappingClass->getEnterpriseMapping() as $edealKey => $formKey){
                $enterprise->$edealKey = $data[$formKey];
            }
            //$client->createEnterprise($enterprise);

            $person = new \StdClass();
            foreach($mappingClass->getPersonMapping() as $edealKey => $formKey){
                $person->$edealKey = $data[$formKey];
            }
            //$client->createPerson($person);

            $couponsWeb = new \StdClass();
            foreach($mappingClass->getCouponsWebMapping() as $edealKey => $formKey){
                $couponsWeb->$edealKey = $data[$formKey];
            }
            //$client->createCouponsWeb_($couponsWeb);

        }
    }

    /**
     * Retrieve mapping class depending on form type
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @return mixed
     */
    private function _getMapping($form)
    {
        $className = "\\Weka\\LeadsExportBundle\\Utils\\Edeal\\" . ucfirst($form->getCode());
        return (class_exists($className)) ? new $className : null;
    }


}