<?php

namespace Weka\LeadsExportBundle\Utils;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;
use Tellaw\LeadsFactoryBundle\Utils\PreferencesUtils;

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
    private $_current_job;
    private $_current_lead;
    protected $_logger = null;

    /** @var  \Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping */
    public $_mappingClass;

    private static $_POST_METHOD_GET_ID_REMPLISSAGE = "GetIdRemplissage";
    private static $_POST_METHOD_GET_ID_CAMPAGNE = "GetIdCampagne";
    private static $_POST_METHOD_GET_ID_COMPTE = "GetIdCompte";
    private static $_POST_METHOD_GET_ID_CONTACT = "GetIdContact";
    private static $_POST_METHOD_GET_ID_PRODUIT = "GetIdProduit";
    private static $_POST_METHOD_CREATE_DRC = "CreateDRC";
    private static $_POST_METHOD_CREATE_AFFAIRE = "CreateAffaire";
    private static $_POST_METHOD_CLOSE_REMPLISSAGE = "CloseRemplissage";

    private static $_AHTENA_ID = "850ba435-4c33-7442-6a27-55ca057fb0c8";

    public $isTestMode = false;

    private $_exportUtils = null;
    private $_functionnalTestingUtils = null;


    public function __construct ()
    {

        PreferencesUtils::registerKey(  "ATHENA_URL",
                                        "Url to Athena CRM Plateform",
                                        PreferencesUtils::$_PRIORITY_REQUIRED);
    }

    public function getExportUtils () {
        if ($this->_exportUtils == null) {
            $this->_exportUtils = $this->getContainer()->get('export_utils');
        }
        return $this->_exportUtils;
    }

    public function getFunctionnalTestingUtils () {
        if ($this->_functionnalTestingUtils == null) {
            $this->_functionnalTestingUtils = $this->getContainer()->get('functionnal_testing.utils');
        }
        return $this->_functionnalTestingUtils;
    }

    public function getLogger () {
        if ( $this->_logger == null) {
            $this->_logger = $this->getContainer()->get('export.logger');
        }
        return $this->_logger;
    }

    public function isDrc ( $data ) {
        if ($this->_formConfig["export"]["athenaV2"]["method"] == "drc") {
            return true;
        } else {
            return false;
        }
    }

    public function isAffaire ( $data ) {
        if ($this->_formConfig["export"]["athenaV2"]["method"] == "affaire") {
            return true;
        } else {
            return false;
        }
    }

    public function isExportable ( $job, $form, $data )
    {

        //on dégage si profil étudiant (TI et WK) ou si type d'établissement Particulier/étudiant (WK)
        // On enlève aussi en cas de test
        $testUtils = $this->container->get("functionnal_testing.utils");

        if ($testUtils->isTestLead($job->getLead())) {

            $this->_exportUtils->updateJob($job, ExportUtils::$_EXPORT_NOT_SCHEDULED, 'TEST - pas d\'export');
            $this->_exportUtils->updateLead($job->getLead(), ExportUtils::$_EXPORT_NOT_SCHEDULED, 'TEST - pas d\'export');
            return false;

        } else if (isset($data['profil']) && strtoupper($data['profil']) == 'ETUDIANT'
            || isset($data['type-etablissement']) && $data['type-etablissement'] == 'particulier_etudiant') {

            $this->_exportUtils->updateJob($job, ExportUtils::$_EXPORT_NOT_SCHEDULED, 'Profil étudiant - pas d\'export');
            $this->_exportUtils->updateLead($job->getLead(), ExportUtils::$_EXPORT_NOT_SCHEDULED, 'Profil étudiant - pas d\'export');
            return false;

        }

        if ($job->getStatus() == ExportUtils::$_EXPORT_MULTIPLE_ERROR) {

            $this->getLogger()->error("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Job ignoré en ERREUR ");
            return false;

        } else if ( is_null($this->_mappingClass) ) {

            $error = 'mapping inexistant pour '.$form->getCode();
            $this->getLogger()->error("[" . $this->_current_job . "]"."[".$this->_current_lead."]"." ATHENAV2 : ".$error);
            $status = $this->_exportUtils->getErrorStatus($job);
            $this->_exportUtils->updateJob($job, $status, $error);
            $this->_exportUtils->updateLead($job->getLead(), $status, $error);

            return false;

        }

        return true;

    }

    public function preProcessData ( $data ) {

        // Jira : ST-5281
        if(array_key_exists('acteur', $this->_formConfig["export"]["athenaV2"])){
            $data["acteur"] = $this->_formConfig["export"]["athenaV2"]["acteur"];
        }

        return $data;

    }

    public function init ( $form ) {

        // Method used to get Athena URL
        $this->_athenaUrl = $this->getContainer()->get("preferences_utils")->getUserPreferenceByKey('ATHENA_URL');

        // Get Utils & Logger
        $this->_exportUtils = $this->getExportUtils();
        $this->_logger = $this->getLogger();

        $this->_formConfig = $form->getConfig();

        // Recuperation de la mapping class
        $this->_mappingClass = $this->_getMapping($form);

    }

    /**
     * Process export
     *
     * @param array $jobs
     * @param Form $form
     */
    public function export($jobs, $form)
    {
        // Method used to init objects
        $this->init($form);

        // Find the ID of the form
        $source = $this->_formConfig["export"]["athenaV2"]["source"];

        $this->_logger->info("############ ATHENAV2 - EXPORT ###############");
        // Loop over export jobs
        foreach($jobs as $job){

            // If testlead, then switch to test mode
            if ($this->getFunctionnalTestingUtils()->isTestLead ( $job->getLead() )) {
                $this->isTestMode = true;
            }

            $data = json_decode($job->getLead()->getData(), true);

            // Filter for preprocessing datas
            $data = $this->preProcessData( $data );

            if ( !$this->isExportable( $job, $form, $data )) {
                continue;
            }

            $this->_current_job = $job->getId();
            $this->_current_lead = $job->getLead()->getId();

            $has_error = false;

            // Get leads' id
            $id_leadsfactory = $job->getLead()->getId();
            $this->_logger->info("############ ATHENAV2 - ID_LEADS (" . $id_leadsfactory . ") ###############");

            // Client informations
            $ip_adr = $job->getLead()->getIpadress();
            $user_agent = $job->getLead()->getUserAgent();

            // Start Session to Athena
            try {
                $id_remplissage = $this->getAthenaRemplissage( $source, $data, $ip_adr, $user_agent );
                $this->_mappingClass->id_remplissage = $id_remplissage;
            } catch (\Exception $e) {
                $has_error = true;
                $message = "Error in getAthenaRemplissage : ".$e->getMessage();
            }

            if (!$has_error) {
                try {
                    // Get ID Campagne
                    $id_campagne = $this->getIdCampagne($id_remplissage, $source, $data);
                    $this->_mappingClass->id_campagne = $id_campagne;
                } catch (\Exception $e) {
                    $has_error = true;
                    $message = "Error in getAthenaRemplissage : ".$e->getMessage();
                }
            }

            if (!$has_error) {
                try {
                    // Get ID Produit
                    $id_produit = $this->getProduit($id_remplissage, $source, $data);
                } catch (\Exception $e) {
                    $has_error = true;
                    $message = "Error in getProduit : ".$e->getMessage();
                }
            }

            if (!$has_error) {
                try {
                    // Get ID Compte
                    $id_compte = $this->getCompte($id_remplissage, $source, $data, $id_campagne);
                } catch (Exception $e) {
                    $has_error = true;
                    $message = "Error in getCompte : ".$e->getMessage();
                }
            }

            if (!$has_error) {
                try {
                    // Get ID Contact
                    $id_contact = $this->getContact($id_remplissage, $source, $data, $id_compte, $id_campagne);
                } catch (\Exception $e) {
                    $has_error = true;
                    $message = "Error in getContact : ".$e->getMessage();
                }
            }

            // Send Request createDRC or createAffaire
            if ( $this->isDrc( $data ) ) {
                if (!$has_error) {
                    try {
                        $requestData = $this->updateDrcData($id_remplissage, $data, $id_campagne, $id_produit, $id_compte, $id_contact, $id_leadsfactory);
                        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_CREATE_DRC, $requestData, $source );
                        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : id_drc -> " . $id_remplissage );
                    } catch (\Exception $e) {
                        $has_error = true;
                        $message = "Error in createDrc : ".$e->getMessage();
                    }
                }
            } else if ($this->isAffaire( $data ) ){
                if (!$has_error) {
                    try {
                        $results = $this->createAffaire($id_remplissage, $data, $id_campagne, $id_produit, $id_compte, $id_contact, $source);
                    } catch (\Exception $e) {
                        $has_error = true;
                        $message = "Error in createAffaire : ".$e->getMessage();
                    }
                }
            }

            if (!$has_error) {
                try {
                    // closing Athena Connection
                    $this->closeAthenaConnection($id_remplissage, $source, $data);
                } catch (\Exception $e) {
                    $has_error = true;
                    $message = "Error in closeAthenaConnection : ".$e->getMessage();
                }
            }

            if(!$has_error ){

                if ($results != NULL && !$this->_hasError($results)) {

                    $log = "Exporté avec succès";
                    $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Exporté avec succès -> id_remplissage : " . $id_remplissage);
                    $status = ExportUtils::$_EXPORT_SUCCESS;

                    $this->_exportUtils->updateJob($job, $status, "Id Athena : ".$id_remplissage);
                    $this->_exportUtils->updateLead($job->getLead(), $status, "Id Athena : ".$id_remplissage);

                    echo ("[J".$this->_current_job."]"."[L".$this->_current_lead."]"."ATHENA V2 : Lead Exporté : ".$id_remplissage."\r\n");

                } else {

                    if ($results) {
                        $log = json_encode($results->errors);
                    } else {
                        $log = "Réponse vide d'Athena";
                    }
                    $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : [Erreur lors de l'export] - ".$log);
                    $status = $this->_exportUtils->getErrorStatus($job);

                    $this->_exportUtils->updateJob($job, $status, $log);
                    $this->_exportUtils->updateLead($job->getLead(), $status, $log);

                    $this->notifyOfExportIssue ( $log, $form, $job, $status );

                    echo ("[J".$this->_current_job."]"."[L".$this->_current_lead."]"."ATHENA V2 : Lead en erreur : ".$log."\r\n");

                }

            } else {

                $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : [Erreur lors de l'export] - " . $message);
                $status = $this->_exportUtils->getErrorStatus($job);

                $this->_exportUtils->updateJob($job, $status, $message);
                $this->_exportUtils->updateLead($job->getLead(), $status, $message);

                $this->notifyOfExportIssue ( $message, $form, $job, $status );

                echo ("[J".$this->_current_job."]"."[L".$this->_current_lead."]"."ATHENAV2 : Lead en erreur : ".$message."\r\n");


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
            $logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Recupération du mapping  : " . $config['export']['athenaV2']['mapping_class']);
            $className = "\\Weka\\LeadsExportBundle\\Utils\\AthenaV2\\" . $scopePath . $config['export']['athenaV2']['mapping_class'];
        }else{
            $logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Recupération du mapping  : " . ucfirst($form->getCode()));
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
                $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Recupération de la methode mappée  -> " .$getter." - ".$athenaKey);
                $entity->$athenaKey = $this->_mappingClass->$getter($data);
            }elseif(!empty($formKey)){
                $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Recupération de la valeur mappée  -> " .$athenaKey);
                if( isset($data[$formKey])) {
                    $this->_logger->info ("[".$this->_current_job."]"."[".$this->_current_lead."]"." --> Valeur -> ".$data[$formKey]);
                    $entity->$athenaKey = $data[$formKey];
                } else {
                    $this->_logger->info ("[".$this->_current_job."]"."[".$this->_current_lead."]"." --> Valeur -> [vide]");
                    $entity->$athenaKey = "";
                }
            }else {
                $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Methode non retrouvée -> " . $getter);
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
        $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Envoie des Leads vers ATHENA -> " . $this->_athenaUrl);
        $rawData = http_build_query(array('entryPoint' => 'gatewayv2', 'data' => $request));
        $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : HTTP Query -> [" . $request ."]" );
        $max_exe_time = 30050; // time in milliseconds
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

        $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Résultat de l'envoie : " . $result);
        return $result;
    }

    private function sendRequest($method, $requestData, $source, $version ="3.0")
    {
        // version dans request et passer en paramétres la variable dans le reste des fonctions
        $request = array(
            "source"    => $source,
            "version"   => $version,
            "method"    => $method,
            "testMode"  => $this->isTestMode,
            "data"      => $requestData
        );

        $this->_logger->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Préparation de la requête d'envoie vers ATHENA");
        $jsonRequest = json_encode($request);

        return json_decode($this->_postToAthena($jsonRequest));
    }

    private function getAthenaRemplissage($source, $data, $ip_adr, $user_agent) {

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);

        $dateTime = new \DateTime();
        $requestData = array();
        $requestData["date_formulaire"] = $dateTime->format("c");
        $requestData["adresse_ip"] = $ip_adr ? $ip_adr : "0.0.0.0"; // Pas obligatoire
        $requestData["user_agent"] = $user_agent ? $user_agent : "Non renseigné"; // Pas obligatoire

        $results = $this->sendRequest(AthenaV2::$_POST_METHOD_GET_ID_REMPLISSAGE, $requestData, $source);
        try {
            $idRemplissage = $results->result->id_remplissage;
        } catch (\Exception $e) {
            throw new Exception ("Response has error from Athena : ".json_encode($results));
        }
        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : id_remplissage -> " . $idRemplissage);

        return $idRemplissage;
    }

    private function getIdCampagne ( $idRemplissage, $source, $data ) {

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);

        $requestData = array();
        $requestData["code_action"] = $data["utmcampaign"]; // Debute par un / "/XX/XX/XXXXXXX"

        // ID used for Athena Linking.
        $requestData["id_remplissage"] = $idRemplissage;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_CAMPAGNE, $requestData, $source);
        try {
            $id_athena = $results->result->id_athena;
        } catch (\Exception $e) {
            throw new Exception ("Response has error from Athena : ".json_encode($results));
        }


        $this->getLogger()->info( "[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : id_campagne (utmcampaign) -> " .$id_athena);
        return $id_athena;

    }

    private function getProduit ( $idRemplissage, $source, $data ) {

        if ( array_key_exists("product_sku", $data) && trim($data["product_sku"])!="") {

            $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);
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
            $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : id_produit -> Aucun produit trouvé");
            return false;
        }

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : id_produit -> " . $id_produit );

        return $id_produit;

    }

    private function getCompte ( $idRemplissage, $source, $data, $id_campagne ) {

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);

        $requestData = $this->getMappedData($data, $this->_mappingClass->getCompteMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;
        $requestData->id_campagne = $id_campagne;

        $results = $this->sendRequest( AthenaV2::$_POST_METHOD_GET_ID_COMPTE, $requestData, $source);

        try {
            $id_compte = $results->result->id_athena;
        } catch (\Exception $e) {
            throw new Exception ("Response has error from Athena : ".json_encode($results));
        }
        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : id_compte -> " . $id_compte );

        return $id_compte;

    }

    private function getContact($idRemplissage, $source, $data, $id_compte, $id_campagne) {


        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);

        $requestData = $this->getMappedData($data, $this->_mappingClass->getContactMapping());

        // ID used for Athena Linking.
        $requestData->id_remplissage = $idRemplissage;
        $requestData->id_compte = $id_compte;
        $requestData->id_campagne = $id_campagne;

        $results = $this->sendRequest(AthenaV2::$_POST_METHOD_GET_ID_CONTACT, $requestData, $source);

        try {
            $id_contact = $results->result->id_athena;
        } catch (\Exception $e) {
            throw new Exception ("Response has error from Athena : ".json_encode($results));
        }

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : id_contact -> " . $id_contact );

        return $id_contact;
    }

    public function updateDrcData($idRemplissage, $data, $id_campagne, $id_produit,
                                  $id_compte, $id_contact, $id_leadsfactory) {

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);;

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

        return $requestData;

    }

    private function createAffaire ( $id_remplissage, $data, $source ) {

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);

        return true;
    }

    private function closeAthenaConnection ( $id_remplissage, $source, $data ) {

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : appel de la methode " . __FUNCTION__);

        $dateTime = new \DateTime();

        $requestData = array();
        $requestData["date_formulaire"] = $dateTime->format("c");
        $requestData["id_remplissage"] = $id_remplissage;

        $idRemplissage = $this->sendRequest( AthenaV2::$_POST_METHOD_CLOSE_REMPLISSAGE, $requestData, $source );

        $this->getLogger()->info("[".$this->_current_job."]"."[".$this->_current_lead."]"." ATHENAV2 : Fin de remplissage");
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
        if (!$result) {
            return false;
        }
        return (count($result->errors) > 0) ? true : false;
    }

}