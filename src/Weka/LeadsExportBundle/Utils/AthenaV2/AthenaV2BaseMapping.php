<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2;


use Doctrine\ORM\EntityManager;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElementRepository;

class AthenaV2BaseMapping {

    protected $em;

    /** @var ReferenceListElementRepository $list_element_repository */
    protected $list_element_repository;

    public $id_campagne = null;
    public $id_remplissage = null;
    public $id_compte = null;
    public $id_contact = null;

    public function __construct(EntityManager $entityManager, ReferenceListElementRepository $list_element_repository)
    {
        $this->em = $entityManager;
        $this->list_element_repository = $list_element_repository;
    }

    public function getProduitMapping () {

        return array (

            "id_athena"         =>  "", // Vide
            "athena_unique_id"  => "",  // Vide ou ID Session si formation
            "code_sap_mpf"      => "",  // Vide
            "code_sap_mi"       => "",  // Vide
            "code_sap_offre"    => "product_sku"

        );

    }

    public function getCompteMapping (  ) {

        return array (

            "id_athena"                 => "",  // Vide
            "id_sap"                    => "",  // Vide (Prevoir evolution si connecte)
            "id_sogec"                  => "",  // Vide
            "raison_sociale"            => "etablissement",
            "id_campagne"               => "",  // Methode de récupération de la données
            "rue_facturation"           => "",
            "code_postal_facturation"    => "zip",
            "dep_region_facturation"    => "",  // Vide
            "ville_facturation"         => "ville",
            "pays_facturation"          => "pays",
            "rue_livraison"             => "",
            "code_postal_livraison"     => "",  // Vide
            "dep_region_livraison"      => "",  // Vide
            "ville_livraison"           => "",  // Vide
            "pays_livraison"            => "",  // Vide
            "effectif_site"             => "",  // Vide
            "fax"                       => "",  // Vide
            "telephone"                 => "phone",
            "email"                     => "email",
            "siret"                     => "",  // Vide
            "naf"                       => "",  // Vide
            "ce"                        => "",  // Vide
            "secteur_activite_tissot_ti_cctp" => "",    // Methode de récuperation de la donnée
            "secteur_activite_weka"     => "",  // Methode de récuperation de la donnée
            "id_web"                    => "",
            "site_web"                  => "",
            "type_compte"               => "",  // Vide
            "date_prochaine_election_ce" => "", // Vide
            "nb_lits"                   => "",  // Vide
            "nb_habitants"              => "",  // Vide
            "tranche_lits"              => "",  // Vide
            "tranche_effectifs"         => "",  // Vide
            "tranche_population"        => "",  // Vide
            "presence_dup"              => "",  // Vide
            "type_client_ec"            => "",  // Vide
            "numero_tva_intra"          => "",  // Vide
            "tab_contact"               => ""   // Methode de récupération des données

        );

    }

    public function getContactMapping () {

        return array (

            "id_athena"                 => "",  // Vide
            "id_sap"                    => "",  // Vide
            "id_sogec"                  => "",  // Vide
            "membre_chsct"              => "",  // Vide
            "delegue_perso"             => "", // Vide
            "membre_dup"                => "", // Vide
            "civilite"                  => "",  // Methode de récupération des données
            "id_campagne"               => "",  // Methode de récupération des données
            "prenom"                    => "firstName",
            "nom"                       => "lastName",
            "fonction_marketing"        => "",  // Methode de récupération des données
            "service"                   => "",  // TODO
            "telephone"                 => "phone",
            "portable"                  => "",  // Vide
            "fax"                       => "",  // Vide
            "email"                     => "email",
            "est_manager"               => "",  // Vide
            "type_utilisation"          => "",  // Vide
            "id_web"                    => "",  // Vide
            "membre_ce"                 => "",  // Vide
            "profil_ti"                 => "",
            "interets_ti"               => "",
            "interets_tissot"           => "",  // Vide
            "profil_weka"               => "",
            "interets_weka"             => "",
            "type_compte_cctp"          => "",  // Vide
            "responsable_prescription_cctp" => "",  // Vide
            "email_valide"              => ""   // Vide

        );

    }

    public function getDRCMapping () {

        $dateTime = new \DateTime();

        return array (

            "detail_demande"            => "comment",  // Vide
            "marque"                    => "",  // Vide
            "deja_client"               => "",  // Vide
            "id_sogec"                  => "",  // Boolean
            "id_sap"                    => "",  // Vide
            "periode_mission"           => "",  // Vide
            "lieu_souhaite"             => "",  // Vide
            "nb_participants"           => "",  // Vide
            "theme_demande"             => "",  // Vide
            "objectif_principal"        => "",  // Vide
            "presentation_projet"       => "",  // Vide
            "attente_eti"               => "",  // Vide
            "rdv_conseiller"            => "",  // Boolean
            "id_compte"                 => "",  // Methode de récupération des données
            "id_contact"                => "",  // Methode de récupération des données
            "type_demande"              => "",  // Methode de récupération des données
            "activite"                  => "",  // Vide
            "id_campagne"               => "",  // Methode de récupération des données
            "id_produit"                => "",  // Methode de récupération des données
            "date_creation"             => $dateTime->format("c"),
            "statut"                    => "",  // Vide
            "id_formation"              => "",  // Vide
            "adapter_formation"         => "",  // Vide
            "region_souhaitee_formation" => "", // Vide
            "detail_demande_formation"  => "",  // Vide
            "dep_souhaite_formation"    => "",  // Vide
            "contexte"                  => "",  // Vide
            "langue"                    => "",  // Vide
            "systeme_exploitation"      => "",  // Vide
            "id_assignation"            => "",   // Vide
            "version"                   => ""   // Methode de récupération des données

        );

    }

    public function getVersion () {
        return "1.0";
    }

    public function getAffaireMapping () {

        return array (

        );



    }

    public function getArticleMapping () {

    }

    /*
    public function getSecteur_activite_weka ($data){

        $secteurs = array (
            "14"    => "academie",
            "19"    => "autres",
            "9"     => "centres_action_sociale",
            "1"     => "conseil_general",
            "2"     => "conseil_regional",
            "18"    => "epic",
            "11"    => "autres", // Hopital privé
            "10"    => "hopitaux_publics",
            "3"     => "communaute_commune",
            "17"    => "lycees", // Lycée privé
            "13"    => "lycees", // Lycée publique
            "8"     => "mairie",
            "4"     => "maisons_retraites", // Privée
            "12"    => "maisons_retraites", // Publique
            "15"    => "ministere",
            "16"    => "prefecture",
            "5"     => "rectorat",
            "7"     => "SEM",
            "6"     => "sivom"
        );
        return $secteurs[$data['type-etablissement']];
    }

    public function getSecteur_activite_tissot_ti_cctp ($data){

        $secteurs = array (
            "12"    => "Autre",
            "15"    => "Autre", // Biomedical Pharma
            "3"     => "Construction",
            "13"    => "ElectroniqueAutomatique",
            "4"     => "Energies",
            "5"     => "EnvironnementSecurite",
            "6"     => "GenieIndustriel",
            "10"    => "Innovations",
            "11"    => "Materiaux",
            "7"     => "Mecanique",
            "1"     => "MesuresAnalyses",
            "2"     => "ProcedesChimieBioAgro",
            "8"     => "SciencesFondamentales",
            "9"     => "TechnologiesDeLInformation",
            "14"    => "Transports",
        );
        return $secteurs[$data['secteur-activite']];
    }
*/
    public function getTab_contact ( $data ) {

        $contact = array (

            "email_contact" => $data["email"],
            "nom"           => $data["lastName"],
            "prenom"        => $data["firstName"],
            "id_contact"    => ""

        );

        return $contact;

    }

    public function getCivilite ( $data ) {

        $civilite = array (

            "MR"    => "m",
            "MRS"   => "mme"

        );

        return $civilite[$data["salutation"]];

    }

    // TODO : A terminer
    public function getFonction_marketing ( $data ) {

        $fonctions = array (

            "animateur_sst_correspondant__scurit" => "",
            "architecte_matre_duvre" => "",
            "assistant_qhse"        => "",
            "bureau_dtude_technique_metreur" => "",
            "charg_de_mission_charg_dtude" => "",
            "chef_de_chantier_opc" => "",
            "chef_de_projet_reach__clp__frd" => "",
            "chef_des_ventes" => "",
            "conducteur_de_travaux" => "",
            "consultant_expert_formateur" => "",
            "directeur_achat" => "",
            "directeur_bureau_dtude_rd" => "",
            "directeur_centre_de_doc_bibliothque" => "",
            "directeur_conditionnement" => "",
            "directeur_de_production"   => "",
            "directeur_de_site" => "",
            "directeur_des_services_techniques" => "",
            "directeur_des_si" => "",
            "directeur_dveloppement" => "",
            "directeur_dusine" => "",
            "directeur_environnement" => "",
            "directeur_industriel" => "",
            "directeur_informatique" => "",
            "directeur_logistique" => "",
            "directeur_maintenance" => "",
            "directeur_mtrologie" => "",
            "directeur_qualit" => "",
            "directeur_rd" => "",
            "directeur_technique" => "",
            "conomiste_de_la_construction" => "",
            "enseignant_chercheur_matre_de_confrence" => "",
            "gestionnaire_de_patrimoine_immobilier" => "",
            "infirmire" => "",
            "ingnieur" => "",
            "ingnieur_commercial_sav" => "",
            "ingenieur_conseil" => "",
            "ingnieur_qhse_ingnieur_environnement" => "",
            "ingnieur_territorial" => "",
            "inspecteur_technique" => "",
            "iprp_hygieniste" => "",
            "mdecin_du_travail" => "",
            "responsable_achat" => "",
            "responsable_affaires_rglementaires" => "",
            "responsable_be_mthode" => "",
            "responsable_de_laboratoire" => "",
            "responsable_de_production" => "",
            "responsable_environnement" => "",
            "responsable_hse_chef_de_service_scurit" => "",
            "responsable_informatique" => "",
            "responsable_logistique__magasin" => "",
            "responsable_maintenance" => "",
            "responsable_maintenance_travaux_neufs_services_gnr..." => "",
            "responsable_marketing" => "",
            "responsable_mthode" => "",
            "responsable_phytosanitaire" => "",
            "responsable_qualit" => "",
            "responsable_rd" => "",
            "responsable_risque_produit_homologation" => "",
            "responsable_systme_dinformation_logiciel" => "",
            "responsable_technique" => "",
            "responsable_urbanisme" => "",
            "technicien_de_laboratoire_technicien_rd" => "",
            "technicien_hse" => "",
            "technicien_oprateur" => "",
            "toxicologue" => "",
            "DirecteurAdministratifFinancier" => "",
            "DirecteurRessourcesHumaines" => "",
            "DirecteurFormation" => "",
            "ResponsableRessourcesHumaines" => "",
            "ResponsableFormation" => "",
            "AssistantRH" => "",
            "AssistantResponsableFormation" => "",
            "SecretaireGeneral" => "",

        );

        return "animateur_sst_correspondant__scurit";

    }


}