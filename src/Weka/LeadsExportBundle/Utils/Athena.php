<?php

namespace Weka\LeadsExportBundle\Utils;

use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;


class Athena extends AbstractMethod{

    /**
     * Url du service Athena
     *
     * @var string
     */
    private $_athenaUrl;

    public function __construct($athenaUrl)
    {
        $this->_athenaUrl = $athenaUrl;
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

        $mappingClass = $this->_getAthenaMapping($form);

        foreach($jobs as $job){

            if(is_null($mappingClass)){
                $job->setLog('Mapping inexistant job ID '.$job->getId());
                $job->setStatus($exportUtils->getErrorStatus($job));

                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($job);
                $em->flush();

                $logger->error('Mapping inexistant job ID '.$job->getId());
                continue;
            }
            $data = json_decode($job->getLead()->getData(), true);
            $request = $this->_buildRequest($data, $mappingClass);
            $result = $this->_postToAthena($request);

            $result = json_decode($result);

            if(!$this->_hasError($result)){
                $log = "Exporté avec succès";
                $status = $exportUtils::$_EXPORT_SUCCESS;
            }else{
                $log = json_encode($result->errors);
                $status = $exportUtils->getErrorStatus($job);
            }
            $exportUtils->updateJob($job, $status, $log);
            $exportUtils->updateLead($job->getLead(), $status, $log);
            $logger->info($log);
        }
    }

    /**
     * Build request
     *
     * @param array $data
     * @param Athena\AbstractMapping $mapping
     * @return string
     */
    private function _buildRequest($data, $mapping)
    {
        $mappingArray = $mapping->getMappingArray();
        $request = '';

        foreach($mappingArray as $mageKey => $athenaKey){
            $getter = 'get'.ucfirst(strtolower($athenaKey));
            if (method_exists($mapping, $getter)){
                $value = urlencode(trim($mapping->$getter($data)));
            }else{
                if(array_key_exists($mageKey, $data)) {
                    $value = urlencode(trim($data[$mageKey]));
                } else {
                    $value = '';
                }
            }
            if($athenaKey == 'emails' || $athenaKey == 'email_1' || $athenaKey == 'email_2' || $athenaKey == 'email_3' || $athenaKey == 'follower_emails'){
                $request .= "&data[".$athenaKey."][0]=".$value;
            }else{
                $request .= "&data[".$athenaKey."]=".$value;
            }
            if($athenaKey == $mapping->getAthenaRequestKey())
                $athenaKeyValue = $value;
        }
        $request = 'entryPoint='.$mapping->getEntryPoint().'&from='.$mapping->getSource().'&authkey='.$mapping->getAuthKey($athenaKeyValue) . $request;
        return $request;
    }

    /**
     * Send request to Athena
     *
     * @param string $request
     * @return mixed
     */
    private function _postToAthena($request)
    {
        $logger = $this->getContainer()->get('export.logger');
        $logger->info('Athena post start==================>');
        $logger->info('Athena URL : '.$this->_athenaUrl);
        $logger->info($request);

        echo ("==> Command line : ".$request."\r\n");

        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->_athenaUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $request);

        $result = curl_exec($ch);

        curl_close($ch);

        $logger->info("Athena Result =====> ");
        $logger->info($result);

        return $result;
    }

    /**
     * Retrieve mapping class depending on form type
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @return mixed
     */
    private function _getAthenaMapping($form)
    {
	    $config = $form->getConfig();

	    if(isset($config['export']['athena']['mapping_class'])){
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Athena\\" . $config['export']['athena']['mapping_class'];
	    }else{
		    $className = "\\Weka\\LeadsExportBundle\\Utils\\Athena\\" . ucfirst($form->getCode());
	    }

        return (class_exists($className)) ? new $className : null;
    }


    private function _hasError($result)
    {
        return (count($result->errors) > 0) ? true : false;
    }

}