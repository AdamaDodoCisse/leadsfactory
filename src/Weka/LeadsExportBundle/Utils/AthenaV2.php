<?php

namespace Weka\LeadsExportBundle\Utils;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;

use Symfony\Component\HttpFoundation\Request;

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

    /**
     * Process export
     *
     * @param array $jobs
     * @param Form $form
     */
    public function export($jobs, $form)
    {
        $this->_athenaUrl = $this->getContainer()->get("preferences_utils")->getUserPreferenceByKey ('ATHENA_URL');

        // Get Utils & Logger
        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getLogger();
        $this->_logger = $logger;

        $this->_formConfig = $form->getConfig();

        // Recuperation de la mapping class
        $this->_mappingClass = $this->_getMapping($form);

        // Find the ID of the form
        $source = $this->_formConfig["export"]["athenaV2"]["source"];

        // Get destination
        $id_assignation = 'Non renseigné.';
        if(array_key_exists('id_assignation', $this->_formConfig["export"]["athenaV2"])){
            $id_assignation = $this->_formConfig["export"]["athenaV2"]["id_assignation"];
        }


        $logger->info("############ ATHENAV2 - EXPORT ###############");
        // Loop over export jobs
        foreach($jobs as $job){
            if(is_null($this->_mappingClass)){

                $error = 'mapping inexistant pour '.$form->getCode();
                $logger->error("ERREUR ATEHENAV2 : ".$error);
                $status = $exportUtils->getErrorStatus($job);
                $exportUtils->updateJob($job, $status, $error);
                $exportUtils->updateLead($job->getLead(), $status, $error);

            } else {

                $data = json_decode($job->getLead()->getData(), true);

                // Get leads' id
                $id_leadsfactory = $job->getLead()->getId();
                $logger->info("############ ATHENAV2 - ID_LEADS (" . $id_leadsfactory . ") ###############");

                // Client informations
                $ip_adr = $job->getLead()->getIpadress();
                $user_agent = $job->getLead()->getUserAgent();

                // Start Session to Athena
                $id_remplissage = $this->getAthenaRemplissage( $source, $data, $ip_adr, $user_agent );
                $this->_mappingClass->id_remplissage = $id_remplissage;

                // Get ID Campagne
                $id_campagne = $this->getIdCampagne( $id_remplissage, $source, $data );
                $this->_mappingClass->id_campagne = $id_campagne;

                // Get ID Produit
                $id_produit = $this->getProduit( $id_remplissage, $source, $data );

                // Get ID Compte
                $id_compte = $this->getCompte( $id_remplissage, $source, $data, $id_campagne );

                // Get ID Contact
                $id_contact = $this->getContact( $id_remplissage, $source, $data, $id_compte, $id_campagne );
                // Send Request createDRC or createAffaire
                if ( $this->_formConfig["export"]["athenaV2"]["method"] == "drc" ) {
                    $results = $this->createDrc( $id_remplissage, $data, $id_campagne, $id_produit, $id_compte,
                        $id_contact, $source, $id_leadsfactory, $id_assignation);
                } else {
                    $results = $this->createAffaire( $id_remplissage, $data, $id_campagne, $id_produit, $id_compte, $id_contact, $source );
                }

                // closing Athena Connection
                $this->closeAthenaConnection( $id_remplissage, $source, $data );

                if(!$this->_hasError($results)){
                    $log = "Exporté avec succès";
                    $logger->info("LOG ATHENAV2 : Exporté avec succès -> id_remplissage : " . $id_remplissage);
                    $status = $exportUtils::$_EXPORT_SUCCESS;
                } else {
                    $log = json_encode($results->errors);
                    $logger->info("LOG ATHENAV2 : Erreur lors de l'export : ".$log);
                    $status = $exportUtils->getErrorStatus($job);
                }
                $exportUtils->updateJob($job, $status, $log);
                $exportUtils->updateLead($job->getLead(), $status, $log);
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
            $logger->info("LOG ATHENAV2 : Recupération du mapping  : " . $config['export']['athenaV2']['mapping_class']);
            $className = "\\Weka\\LeadsExportBundle\\Utils\\AthenaV2\\" . $scopePath . $config['export']['athenaV2']['mapping_class'];
        }else{
            $logger->info("LOG ATHENAV2 : Recupération du mapping  : " . ucfirst($form->getCode()));
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
                $this->_logger->info("LOG ATHENAV2 : Recupération de la methode mappée  -> " .$getter." - ".$athenaKey);
                $entity->$athenaKey = $this->_mappingClass->$getter($data);
            }elseif(!empty($formKey)){
                $this->_logger->info("LOG ATHENAV2 : Recupération de la methode mappée  -> " .$athenaKey);
                $entity->$athenaKey = isset($data[$formKey]) ? $data[$formKey] : null;
            }else {
                $this->_logger->info("LOG ATHENAV2 : Methode non retrouvée -> " . $getter);
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
        $this->_logger->info("LOG ATHENAV2 : Envoie des Leads vers ATHENA -> " . $this->_athenaUrl);

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

        $this->_logger->info("LOG ATHENAV2 : Résultat de l'envoie : " . $result);
        return $result;
    }

    private function sendRequest($method, $requestData, $source, $version ="2.0")
    {  // version dans request et passer en paramétres la variable dans le reste des fonctions

        $request = array(
            "source"    => $source,
            "version"   => $version,
            "method"    => $method,
            "data"      => $requestData
        );

        $this->_logger->info("LOG ATHENAV2 : Préparation de la requête d'envoie vers ATHENA");
        $jsonRequest = json_encode($request);
        return json_decode($this->_postToAthena($jsonRequest));
    }

    private function getAthenaRemplissage($source, $data, $ip_adr, $user_agent) {

        $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);

        $dateTime = new \DateTime();
        $requestData = array();
        $requestData["date_formulaire"] = $dateTime->format("c");
        $requestData["adresse_ip"] = $ip_adr ? $ip_adr : "0.0.0.0"; // Pas obligatoire
        $requestData["user_agent"] = $user_agent ? $user_agent : "Non renseigné"; // Pas obligatoire

        $results = $this->sendRequest(AthenaV2::$_POST_METHOD_GET_ID_REMPLISSAGE, $requestData, $source);
        $idRemplissage = $results->result->id_remplissage;

        $this->getLogger()->info("LOG ATHENAV2 : id_remplissage -> " . $idRemplissage);

        return $idRemplissage;
    }

    private function getIdCampagne ( $idRemplissage, $source, $data ) {

        $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);

        $requestData = array();
        $requestData["code_action"] = $data["utmcampaign"]; // Debute par un / "/XX/XX/XXXXXXX"

        // ID used for Athena Linking.
        $requestData["id_remplissage"] = $idRemplissage;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_CAMPAGNE, $requestData, $source);
        $id_athena = $results->result->id_athena;

        $this->getLogger()->info( "LOG ATHENAV2 : id_campagne (utmcampaign) -> " .$id_athena);

        return $id_athena;

    }

    private function getProduit ( $idRemplissage, $source, $data ) {

        if ( array_key_exists("product_sku", $data) && trim($data["product_sku"])!="") {

            $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);

            $requestData = $this->getMappedData($data, $this->_mappingClass->getProduitMapping());

            // ID used for Athena Linking.
            $requestData->id_remplissage = $idRemplissage;

            $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_PRODUIT, $requestData, $source );

            if (isset($results->result->id_athena)) {
                $id_produit = $results->result->id_athena;
            } else {
                $id_produit = "";
            }
        } else {
            $this->getLogger()->info("LOG ATHENAV2 : id_produit -> Aucun produit trouvé");
            return false;
        }

        $this->getLogger()->info("LOG ATHENAV2 : id_produit -> " . $id_produit );

        return $id_produit;

    }

    private function getCompte ( $idRemplissage, $source, $data, $id_campagne ) {

        $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);

        $requestData = $this->getMappedData($data, $this->_mappingClass->getCompteMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;
        $requestData->id_campagne = $id_campagne;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_COMPTE, $requestData, $source);
        $id_compte = $results->result->id_athena;

        $this->getLogger()->info("LOG ATHENAV2 : id_compte -> " . $id_compte );

        return $id_compte;

    }

    private function getContact($idRemplissage, $source, $data, $id_compte, $id_campagne) {

        $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);

        $requestData = $this->getMappedData($data, $this->_mappingClass->getContactMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;
        $requestData->id_compte = $id_compte;
        $requestData->id_campagne = $id_campagne;

        $results = $this->sendRequest(AthenaV2::$_POST_METHOD_GET_ID_CONTACT, $requestData, $source);
        $id_contact = $results->result->id_athena;

        $this->getLogger()->info("LOG ATHENAV2 : id_contact -> " . $id_contact );

        return $id_contact;
    }

    private function createDrc($idRemplissage, $data, $id_campagne, $id_produit,
                               $id_compte, $id_contact, $source, $id_leadsfactory, $id_assignation) {

        $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);;

        $requestData = $this->getMappedData($data, $this->_mappingClass->getDRCMapping());
        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;
        $requestData->id_campagne = $id_campagne;
        if ($id_produit) {
            $requestData->id_produit = $id_produit;
        }

        $requestData->id_compte = $id_compte;
        $requestData->id_contact = $id_contact;
        $requestData->id_leadsfactory = $id_leadsfactory;
        $requestData->id_assignation = $id_assignation;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_CREATE_DRC, $requestData, $source );
        $id_drc = $results->result->id_athena;

        $this->getLogger()->info("LOG ATHENAV2 : id_drc -> " . $id_drc );

        return $results;

    }

    private function createAffaire ( $id_remplissage, $data, $source ) {

        $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);

        return true;
    }

    private function closeAthenaConnection ( $id_remplissage, $source, $data ) {

        $this->getLogger()->info("LOG ATHENAV2 : appel de la methode " . __FUNCTION__);

        $dateTime = new \DateTime();

        $requestData = array();
        $requestData["date_formulaire"] = $dateTime->format("c");
        $requestData["id_remplissage"] = $id_remplissage;

        $idRemplissage = $this->sendRequest( AthenaV2::$_POST_METHOD_CLOSE_REMPLISSAGE, $requestData, $source );

        $this->getLogger()->info("LOG ATHENAV2 : Fin de remplissage");
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