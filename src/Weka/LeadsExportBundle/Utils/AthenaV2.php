<?php

namespace Weka\LeadsExportBundle\Utils;

use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;

/**
 * Class AthenaV2
 * Eric Wallet @ 9/6/2015
 *
 * Connecteur V2 Athena
 *
 * @package Weka\LeadsExportBundle\Utils
 */
class AthenaV2 extends AbstractMethod{

    /**
     * Url du service Athena
     *
     * @var string
     */
    private $_athenaUrl;
    private $_formConfig;
    protected $_logger = null;

    /** @var  \Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping */
    private $_mappingClass;

    private static $_POST_METHOD_GET_ID_REMPLISSAGE = "GetIdRemplissage";
    private static $_POST_METHOD_GET_ID_CAMPAGNE = "GetIdCampagne";
    private static $_POST_METHOD_GET_ID_COMPTE = "GetIdCompte";
    private static $_POST_METHOD_GET_ID_CONTACT = "GetIdContact";
    private static $_POST_METHOD_GET_ID_PRODUIT = "GetIdProduit";
    private static $_POST_METHOD_CREATE_DRC = "CreateDRC";
    private static $_POST_METHOD_CREATE_AFFAIRE = "CreateAffaire";
    private static $_POST_METHOD_CLOSE_REMPLISSAGE = "CloseRemplissage";

    public function getLogger () {
        if ( $this->_logger == null) {
          $this->_logger = $this->getContainer()->get('export.logger');
        }
        return $this->_logger;
    }

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
        // Get Utils & Logger
        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getLogger();
        $this->_logger = $logger;

        $this->_formConfig = $form->getConfig();

        // Recuperation de la mapping class
        $this->_mappingClass = $this->_getMapping($form);


        // Find the ID of the form
        $source = $this->_formConfig["export"]["athenaV2"]["source"];

        // Loop over export jobs
        foreach($jobs as $job){

            if(is_null($this->_mappingClass)){
                $error = 'Mapping ATHENAV2 inexistant pour '.$form->getCode();
                $logger->error("Erreur d'export : ".$error);
                $status = $exportUtils->getErrorStatus($job);
                $exportUtils->updateJob($job, $status, $error);
                $exportUtils->updateLead($job->getLead(), $status, $error);
            } else {

                $data = json_decode($job->getLead()->getData(), true);

                // Start Session to Athena
                $logger->info("Calling Athena GetRemplissage");
                $id_remplissage = $this->getAthenaRemplissage( $source, $data );
                $this->_mappingClass->id_remplissage = $id_remplissage;

                // Get ID Campagne
                $logger->info("Calling Athena GetCampagne");
                $id_campagne = $this->getIdCampagne( $id_remplissage, $source, $data );
                $this->_mappingClass->id_campagne = $id_campagne;

                // Get ID Produit
                $logger->info("Calling Athena GetProduit");
                $id_produit = $this->getProduit( $id_remplissage, $source, $data );

                // Get ID Compte
                $logger->info("Calling Athena GetCompte");
                $id_compte = $this->getCompte( $id_remplissage, $source, $data, $id_campagne );

                // Get ID Contact
                $logger->info("Calling Athena GetContact");
                $id_contact = $this->getContact( $id_remplissage, $source, $data, $id_compte );

                // Send Request createDRC or createAffaire
                if ( $this->_formConfig["export"]["athenaV2"]["method"] == "drc" ) {
                    $logger->info("Calling Athena CreateDRC");
                    $results = $this->createDrc( $id_remplissage, $data, $id_campagne, $id_produit, $id_compte, $id_contact, $source );
                } else {
                    $logger->info("Calling Athena CreateAffaire");
                    $results = $this->createAffaire( $id_remplissage, $data, $id_campagne, $id_produit, $id_compte, $id_contact, $source );
                }

                // closing Athena Connection
                $logger->info("Calling Athena CloseRemplissage");
                $this->closeAthenaConnection( $id_remplissage, $source, $data );

                if(!$this->_hasError($results)){
                    $log = "Exporté avec succès";
                    $logger->info("Exporté avec succès");
                    $status = $exportUtils::$_EXPORT_SUCCESS;
                }else{
                    $log = json_encode($result->errors);
                    $logger->info("Erreur d'export : ".$log);
                    $status = $exportUtils->getErrorStatus($job);
                }
                
                $exportUtils->updateJob($job, $status, $log);
                $exportUtils->updateLead($job->getLead(), $status, $log);
                $logger->info($log);

            }

        }
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
        $logger = $this->getLogger();

        $config = $form->getConfig();
        $scope = !is_null($form->getScope()) ? $form->getScope()->getCode() : null;
        $scopePath = !is_null($scope) ? $scope."\\" : '';

        if(isset($config['export']['athenaV2']['mapping_class'])){
            $logger->info($config['export']['athenaV2']['mapping_class']);
            $className = "\\Weka\\LeadsExportBundle\\Utils\\AthenaV2\\" . $scopePath . $config['export']['athenaV2']['mapping_class'];
        }else{
            $className = "\\Weka\\LeadsExportBundle\\Utils\\AthenaV2\\" . $scopePath . ucfirst($form->getCode());
        }
        $em = $this->getContainer()->get('doctrine')->getManager();
        $list_element_repository = $this->getContainer()->get('leadsfactory.reference_list_element_repository');
        return (class_exists($className)) ? new $className($em, $list_element_repository) : null;
    }

    private function getMappedData($data, $mapping)
    {
        $entity = new \StdClass();
        foreach($mapping as $athenaKey => $formKey) {

            $getter = 'get'.ucfirst(strtolower($athenaKey));

            if (method_exists($this->_mappingClass, $getter)){
                $this->_logger->info ("Key method (".$getter." : ".$athenaKey);
                $entity->$athenaKey = $this->_mappingClass->$getter($data);
            }elseif(!empty($formKey)){
                $this->_logger->info ("Key value : ".$athenaKey);
                $entity->$athenaKey = isset($data[$formKey]) ? $data[$formKey] : null;
            }else{
                $entity->$athenaKey = null;
            }
        }
        return $entity;
    }

    /**
     * Send request to Athena
     *
     * @param string $request
     * @return mixed
     */
    private function _postToAthena($request)
    {
        $logger = $this->getLogger();
        $logger->info('Athena post start==================>');
        $logger->info('Athena URL : '.$this->_athenaUrl);
        $logger->info($request);

        $rawData = http_build_query(array('entryPoint' => 'gatewayv2', 'data' => $request));
        $max_exe_time = 10050; // time in milliseconds
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_athenaUrl);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $max_exe_time);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);


        $logger->info("Athena Result =====> ");
        $logger->info($result);
        $logger->info("=====> End of Athena Post & Results  ");

        return $result;
    }

    private function sendRequest ( $method, $requestData, $source ) {

        $request = array (
            "source" => $source,
            "version" => "1.0",
            "method" => $method,
            "data" => $requestData
        );

        $jsonRequest = json_encode( $request );

        return json_decode($this->_postToAthena( $jsonRequest ));

    }

    private function getAthenaRemplissage ( $source, $data ) {

        $this->getLogger()->info( "[remplissage] : ***");
        $this->getLogger()->info( "[remplissage] : *** REMPLISSAGE");
        $this->getLogger()->info( "[remplissage] : ***");
        $dateTime = new \DateTime();

        $requestData = array();
        $requestData["date_formulaire"] = $dateTime->format("c");
        $requestData["adresse_ip"] = "0.0.0.0"; // Pas obligatoire
        $requestData["user_agent"] = "La mere a titi"; // Pas obligatoire

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_REMPLISSAGE, $requestData, $source );
        $idRemplissage = $results->result->id_remplissage;

        $this->getLogger()->info( "[remplissage] : ******** => ID REMPLISSAGE : ".$idRemplissage);

        return $idRemplissage;
    }

    private function getIdCampagne ( $idRemplissage, $source, $data ) {

        $this->getLogger()->info( "[getIdCampagne] ***");
        $this->getLogger()->info( "[getIdCampagne] *** CAMPAGNE");
        $this->getLogger()->info( "[getIdCampagne] ***");

        $requestData = array();
        $requestData["code_action"] = $data["utmcampaign"]; // Debute par un / "/XX/XX/XXXXXXX"

        // ID used for Athena Linking.
        $requestData["id_remplissage"] = $idRemplissage;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_CAMPAGNE, $requestData, $source );
        $id_athena = $results->result->id_athena;

        $this->getLogger()->info( "[getIdCampagne] ******** => ID ATHENA : ".$id_athena);

        return $id_athena;

    }

    private function getProduit ( $idRemplissage, $source, $data ) {

        $this->getLogger()->info( "[getProduit] ***");
        $this->getLogger()->info( "[getProduit] *** PRODUIT");
        $this->getLogger()->info( "[getProduit] ***");

        $requestData = $this->getMappedData($data, $this->_mappingClass->getProduitMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_PRODUIT, $requestData, $source );

        if (isset($results->id_athena)) {
            $id_produit = $results->result->id_athena;
        } else {
            $id_produit = "";
        }

        $this->getLogger()->info( "[getProduit] ******** => ID ATHENA : ".$id_produit);

        return $id_produit;

    }

    private function getCompte ( $idRemplissage, $source, $data, $id_campagne ) {

        $this->getLogger()->info( "[getCompte] ***");
        $this->getLogger()->info( "[getCompte] *** COMPTE");
        $this->getLogger()->info( "[getCompte] ***");

        $requestData = $this->getMappedData($data, $this->_mappingClass->getCompteMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_COMPTE, $requestData, $source );
        $id_compte = $results->result->id_athena;

        $this->getLogger()->info( "[getCompte] ******** => ID ATHENA : ".$id_compte);

        return $id_compte;

    }

    private function getContact ( $idRemplissage, $source, $data, $id_compte ) {

        $this->getLogger()->info( "[getContact] ***");
        $this->getLogger()->info( "[getContact] *** CONTACT");
        $this->getLogger()->info( "[getContact] ***");

        $requestData = $this->getMappedData($data, $this->_mappingClass->getContactMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;
        $requestData->id_compte = $id_compte;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_CONTACT, $requestData, $source );
        $id_contact = $results->result->id_athena;

        $this->getLogger()->info( "[getContact] ******** => ID ATHENA : ".$id_contact);

        return $id_contact;

    }

    private function createDrc ( $idRemplissage, $data, $id_campagne, $id_produit, $id_compte, $id_contact, $source ) {

        $this->getLogger()->info( "[createdrc] : ***");
        $this->getLogger()->info( "[createdrc] : *** DRC");
        $this->getLogger()->info( "[createdrc] : ***");

        $requestData = $this->getMappedData($data, $this->_mappingClass->getDRCMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;
        $requestData->id_campagne = $id_campagne;
        $requestData->id_produit = $id_produit;
        $requestData->id_compte = $id_compte;
        $requestData->id_contact = $id_contact;

        $this->_logger->info( "[createdrc] : id_produit : ".$id_produit);
        $this->_logger->info( "[createdrc] : id_compte : ".$id_compte);
        $this->_logger->info( "[createdrc] : id_contact : ".$id_contact);

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_CREATE_DRC, $requestData, $source );
        $id_drc = $results->result->id_athena;

        $this->getLogger()->info( "[createdrc] : ******** => ID ATHENA : ".$id_drc);

        return $results;

    }

    private function createAffaire ( $id_remplissage, $data, $source ) {

        $this->getLogger()->info( "[affaire] : ***");
        $this->getLogger()->info( "[affaire] : *** AFFAIRE");
        $this->getLogger()->info( "[affaire] : ***");

    }

    private function closeAthenaConnection ( $id_remplissage, $source, $data ) {

        $dateTime = new \DateTime();

        $requestData = array();
        $requestData["date_formulaire"] = $dateTime->format("c");
        $requestData["id_remplissage"] = $id_remplissage;

        $idRemplissage = $this->sendRequest( AthenaV2::$_POST_METHOD_CLOSE_REMPLISSAGE, $requestData, $source );

        return $idRemplissage;

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

    private function _hasError($result)
    {
        return (count($result->errors) > 0) ? true : false;
    }

}