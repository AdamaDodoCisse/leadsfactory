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
            "id_athena"         => "", // Vide
            "athena_unique_id"  => "",  // Vide ou ID Session si formation
            "code_sap_mpf"      => "",  // Vide
            "code_sap_mi"       => "",  // Vide
            "code_sap_offre"    => "product_sku"
        );

    }

    public function getCompteMapping ( ) {

        return array (
            "id_athena"                 => "",  // Vide
            "id_sap"                    => "",  // Vide (Prevoir evolution si connecte)
            "id_sogec"                  => "",  // Vide
            "raison_sociale"            => "etablissement",
            "id_campagne"               => "",  // Methode de récupération de la données
            "rue_facturation"           => "address",
            "code_postal_facturation"   => "zip",
            "dep_region_facturation"    => "",  // Vide
            "ville_facturation"         => "",  // Méthode de récupération de la données
            "nb_habitants"              => "",  // Méthode de récupération de la données
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
            "tranche_lits"              => "",  // Vide
            "tranche_effectifs"         => "",  // Vide
            "tranche_population"        => "",  // Vide
            "presence_dup"              => "",  // Vide
            "type_client_ec"            => "",  // Vide
            "numero_tva_intra"          => "",  // Vide
            "tab_contact"               => "",   // Methode de récupération des données
            "stop_email"                => "",  // Methode de récupération des données
            "stop_mail"                 => "",
            "stop_fax"                  => "",
            "stop_phoning"              => "",
            "stop_global"               => "",
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
            "service"                   => "",  // Methode de récupération des données
            "telephone"                 => "phone",
            "portable"                  => "",  // Vide
            "fax"                       => "",  // Vide
            "email"                     => "email",
            "est_manager"               => "",  // Vide
            "type_utilisation"          => "",  // Vide
            "id_web"                    => "",  // Vide
            "membre_ce"                 => "",  // Vide
            "stop_email"                => "",  // Methode de récupération des données
            "stop_mail"                 => "",
            "stop_fax"                  => "",
            "stop_phoning"              => "",
            "stop_global"               => "",
            "profil_ti"                 => "",  // Methode de récupération des données
            "interets_ti"               => "",
            "interets_tissot"           => "",  // Vide
            "profil_weka"               => "",
            "interets_weka"             => "",
            "type_compte_cctp"          => "",  // Vide
            "responsable_prescription_cctp" => "",  // Vide
            "email_valide"              => "",   // Methode de récupération des données
            "tissot_panel_membre"       => "",
            "tissot_panel_inscription"   => "",
            "tissot_panel_gamme"        => "",
            "tissot_panel_rdv"          => "",
            "tissot_panel_appels"       => "",
            "tissot_panel_groupe"       => "",
            "tissot_panel_date_sollicitation" => "",

        );

    }

    /**
     * Refactored 18-1 for Athena V3
     *
     * @return array
     */
    public function getDRCMapping () {

        $dateTime = new \DateTime();

        return array (
            "id_leadsfactory"           => "id_leadsfactory",
            "detail_demande"            => "",  // Vide
            "marque"                    => "",  // Vide
            "deja_client"               => "deja-client",
            "id_sogec"                  => "",  // Boolean
            "id_sap"                    => "",  // Vide
            "periode_mission"           => "",  // Vide
            "lieu_souhaite"             => "",  // Vide
            "nb_participants"           => "",  // Vide
            "theme_demande"             => "",  // Vide
            "objectif_principal"        => "",  // Vide
            "presentation_projet"       => "",  // Vide
            "attente_eti"               => "",  // Vide
            "rdv_conseiller"            => "demande-rdv",  // Boolean /
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
            "version"                   => "",   // Methode de récupération des données
            "utmmedium"                 => "",   //
            "utmcontent"                => "",   //
            "referrer_url"              => "",   //
            "redirect_url"              => "",   //

            // Jira : ST-5281
            "acteur"                    => "acteur",

            "trackin_origin"            => "",   //

            // Ajout des méthodes V3
            "civilite"                  => "",   // methode de récupération des données
            "nom_contact"               => "lastName",
            "prenom_contact"            => "firstName",
            "email"                     => "email",
            "service"                   => "",  // methode de récupération des données
            "secteur_activite_weka"     => "",  // methode de récupération des données
            "fonction_marketing"        => "",  // methode de récupération des données
            "telephone"                 => "phone",
            "telephone_mobile"          => "",
            "rue_facturation"           => "address",
            "code_postal_facturation"   => "zip",
            "ville_facturation"         => "",  // methode de récupération des données
            "pays_facturation"          => "pays",
            "sku_produit"               => "product_sku",
            "thematique_weka"           => "", // Information inconnue
            "nom_compte"                => "etablissement"

        );

    }

    // #########################################################################################
    // OVERRIDE DES GETTERS
    // #########################################################################################



    public function getTelephone($data) {
        $telephone = "";
        if (array_key_exists("phone", $data) && $data["phone"]) {
            $telephone = $data["phone"];
            if (array_key_exists("pays", $data) && $data["pays"]) {
                switch ($data['pays'])
                {
                    case 'FR':
                        $telephone = '+33' . $data['phone'];
                        break;
                    case 'BE':
                        $telephone = '+32' . $data['phone'];
                        break;
                    case 'MC':
                        $telephone = '+377' . $data['phone'];
                        break;
                    case 'LU':
                        $telephone = '+352' . $data['phone'];
                        break;
                    case 'CH':
                        $telephone = '+41' . $data['phone'];
                        break;
                    default :
                        $telephone = $data['phone'];
                        break;
                }
            }
        }
        return $telephone;
    }

    public function getVille_facturation($data){
        $ma_ville = null;

        if(array_key_exists('ville_id', $data) && $data['ville_id']){
            $ma_ville = $this->list_element_repository->getNameUsingListCodeAndValue("ville", $data['ville_id']);
        } else if (array_key_exists("ville", $data) && $data['ville']){
            $ma_ville = $this->list_element_repository->getNameUsingListCodeAndValue("ville", $data['ville']);
        } else if(array_key_exists("ville_text", $data) && $data['ville_text']){
            $ma_ville = $data['ville_text'];
        }

        // Data treatment
        if (is_array($ma_ville)) {
            if (count($ma_ville) < count($ma_ville, COUNT_RECURSIVE))
                $ma_ville = $ma_ville[0]['name'];
            else // If array is NOT multidimensional
                $ma_ville = $ma_ville['name'];
        }

        return $ma_ville;
    }

    public function getNb_habitants($data){

        $population = "";
        if (array_key_exists( 'zip', $data ) && $data['zip']) {
                $first_str = substr($data['zip'], 0, 1);
            if($first_str == 0){
                $zip_code =  substr($data['zip'], 1);
            } else {
                $zip_code = $data['zip'];
            }

            if (array_key_exists('ville_id', $data) && $data['ville_id']){
                $population = $this->list_element_repository->getValueUsingListCodeAndName("nbhabitants", $zip_code."-".$data['ville_id']);
            } else if(array_key_exists('ville', $data) && $data['ville']){
                $population = $this->list_element_repository->getValueUsingListCodeAndName("nbhabitants", $zip_code."-".$data['ville']);
            } else if(array_key_exists('ville_text', $data) && $data['ville_text']){
                $ville_id = $this->list_element_repository->getValueUsingListCodeAndName("ville", $zip_code."-".$data['ville_text']);
                $population = $this->list_element_repository->getValueUsingListCodeAndName("nbhabitants", $ville_id);
            }
        }

        $pop = "";
        if(is_array($population) && count($population) && array_key_exists('value', $population[0])){
            $pop = $population[0]['value'];
        } else if (is_string($population)) {
            $pop = $population;
        }
        return $pop;
    }

    public function getStop_email($data) {
        $Stop_email = array(
            "0" => "0",
            "1" => "1",
            "2" => "2"
        );
        if (array_key_exists('cnilTi', $data) && $data['cnilTi'] == 1) {
            if (array_key_exists('cnilPartners', $data) && $data['cnilPartners'] == 1) {
                $return = $Stop_email['2'];
            } else {
                $return = $Stop_email['1'];
            }
        } else {
            $return = $Stop_email['0'];
        }
        return $return;
    }

    public function getDetail_demande($data){
        $comment =  "Une demande a été faite ";

        if (array_key_exists("firstName",$data) && $data["firstName"]
            && array_key_exists("lastName",$data) && $data["lastName"]) {
            $comment .= "par : " . ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
        }

        if (array_key_exists("etablissement",$data) && $data["etablissement"]) {
            $comment .= ", société : " . strtoupper($data['etablissement']) ;
        }

        $telephone = $this->getTelephone($data);
        if ($telephone) {
            $comment .= ", telephone : " . strtoupper($telephone);
        }

        if (array_key_exists("product_name",$data) && $data["product_name"]) {
            $comment .= ". A propos du produit : " . $data["product_name"];
        }

        if (array_key_exists("thematique", $data) && $data["thematique"]) {
            $comment .= ". Thematique : ".$data["thematique"];
        }

        if (array_key_exists("comment", $data) && $data["comment"]) {
            $comment .= ". Commentaire : ".$data["comment"] . ".";
        }

        //JIRA  : ST-5283
        if (array_key_exists("type-etablissement", $data) && $data["type-etablissement"]) {
            $comment .= "\nType d'établissement : " . $this->getSecteur_activite_weka($data);
        }

        if (array_key_exists("livre-blanc", $data) && $data["livre-blanc"]) {
            $comment .= "\nLivre blanc : " . $data['livre-blanc'];
        }

        return $comment;
    }

    public function getRdv_conseiller( $data ){
        if (array_key_exists("demande-rdv",$data)) {
            return $data["demande-rdv"] ? true : false;
        }
        return false;
    }

    public function getVersion () {
        return "2.0";
    }

    public function getEmail_valide(){
        return true;
    }


    public function getAffaireMapping () {
        return array ();
    }

    public function getArticleMapping () {
    }


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
            "6"     => "sivom",
            'conseil_departemental'     => 'conseil_departemental',
            'syndicat_intercommunal'    => 'syndicat_intercommunal',
            'hopital'                   => 'hopital',
            'enseignement'              => 'enseignement',
            'sdis'                      => 'sdis',
            'maisons_retraites'         => 'maisons_retraites',
            'acteurs_prives'            => 'acteurs_prives',
            'particulier_etudiant'      => 'particulier_etudiant',
            'association'               => 'association'
        );

        if (array_key_exists("type-etablissement", $data) && $data['type-etablissement']
            && array_key_exists($data['type-etablissement'], $secteurs)) {
            return $secteurs[$data['type-etablissement']];
        }
        return "";
    }


    public function getSecteur_activite_tissot_ti_cctp ($data){

        $secteurs = array (
            "12"    => "Autre",
            "15"    => "BiomedicalPharma", // ce champs est manquant dans le liste ti_titles_list
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


        if (array_key_exists("secteur-activite", $data) && $data["secteur-activite"]
            && array_key_exists($data["secteur-activite"], $secteurs)) {
            return $secteurs[$data["secteur-activite"]];
        }
        return "";
    }


    public function getTab_contact ( $data ) {

        $d_email = array_key_exists("email", $data) ? $data["email"] : "";
        $d_nom = array_key_exists("lastName", $data) ? $data["lastName"] : "";
        $d_prenom = array_key_exists("firstName", $data) ? $data["firstName"] : "";
        $contact = array (
            "email_contact" => $d_email,
            "nom"           => $d_nom,
            "prenom"        => $d_prenom,
            "id_contact"    => ""
        );
        return $contact;
    }

    public function getCivilite ( $data ) {

        $civilite = array (
            "MR"    => "m",
            "MRS"   => "mme"
        );

        if (array_key_exists("salutation",$data) && $data["salutation"]
            && array_key_exists($data["salutation"], $civilite)) {
            return $civilite[$data["salutation"]];
        }
        return "";
    }

    public function getProfil_ti ( $data ) {

        $profil_ti = array (
            "ETUDIANT"      => "ETUDIANT",
            "PROFESSIONNEL" => "PROFESSIONNEL",
            "PARTICULIER" => "PARTICULIER"
        );

        if (array_key_exists("profil",$data) && $data["profil"]
            && array_key_exists($data["profil"], $profil_ti)) {
            return $profil_ti[$data["profil"]];
        }
        return "";
    }


    public function getService($data){
        $fonctions = array(
            "achats_marches_publics" => "achats_marches_publics",
            "affaires_scolaires" => "affaires_scolaires",
            "affaires_sociales_ccas" => "affaires_sociales_ccas",
            "communication" => "communication",
            "culture" => "culture",
            "direction_administrative" => "direction_administrative",
            "documentation_archivage" => "documentation_archivage",
            "education_nationale" => "education_nationale",
            "elections_etat_civil" => "elections_etat_civil",
            "elus" => "elus",
            "environnement" => "environnement",
            "finances" => "finances",
            "insertion" => "insertion",
            "jeunesse_sport" => "jeunesse_sport",
            "juridique" => "juridique",
            "personnes_agees" => "personnes_agees",
            "personnes_handicapees" => "personnes_handicapees",
            "petite_enfance" => "petite_enfance",
            "police_pompiers" => "police_pompiers",
            "politique_de_la_ville_integration" => "politique_de_la_ville_integration",
            "professions_independants" => "professions_independants",
            "protection_de_lenfance" => "protection_de_lenfance",
            "ressources_humaines" => "ressources_humaines",
            "sante" => "sante",
            "sante_au_travail_prevention" => "sante_au_travail_prevention",
            "services_techniques" => "services_techniques",
        );

        if (array_key_exists("service",$data) && $data["service"]
            && array_key_exists($data["service"], $fonctions)) {
            return $fonctions[$data["service"]];
        }
        return "";
    }



    // TODO : A terminer
    public function getFonction_marketing ( $data ) {

        $fonctions = array (
            // weka
            "1er_adjoint_au_maire"  => "1er_adjoint_au_maire",
            "acfi" => "acfi",
            "adjoint_au_maire"      => "adjoint_au_maire",
            "administrateurrgisseur" => "administrateurrgisseur",
            "agent_comptable" => "agent_comptable",
            "aide__domicile" => "aide__domicile",
            "aide_personnes_ges" => "aide_personnes_ges",
            "animateur_de_relais_assistantes_maternelles" => "animateur_de_relais_assistantes_maternelles",
            "assistant_maternel" => "assistant_maternel",
            "assistant_prvention" => "assistant_prvention",
            "assistant_social" => "assistant_social",
            "auxilliaire_de_vie_sociale" => "auxilliaire_de_vie_sociale",
            "auxilliaire_griatrie" => "auxilliaire_griatrie",
            "auxilliaire_grontologie" => "auxilliaire_grontologie",
            "avocat" => "avocat",
            "cadre_socio_ducatif" => "cadre_socio_ducatif",
            "charg_de_mission" => "charg_de_mission",
            "chef_dtat_major" => "chef_dtat_major",
            "chef_de_travauxresp_pdagogique" => "chef_de_travauxresp_pdagogique",
            "coach" => "coach",
            "commandant_de_gendarmerie" => "commandant_de_gendarmerie",
            "conseiller_prvention" => "conseiller_prvention",
            "consultant" => "consultant",
            "contrleur_de_gestion" => "contrleur_de_gestion",
            "coordonateur_petite_enfance" => "coordonateur_petite_enfance",
            "cpe_conseiller_princ_education" => "cpe_conseiller_princ_education",
            "daf__resp_administratif_et_financier" => "daf__resp_administratif_et_financier",
            "dlgu_du_personnel" => "dlgu_du_personnel",
            "dlgu_syndical" => "dlgu_syndical",
            "dgs_dir_gnral_des_services" => "dgs_dir_gnral_des_services",
            "dir_artistique" => "dir_artistique",
            "dir_centre_de_loisirs" => "dir_centre_de_loisirs",
            "dir_centre_de_vacances" => "dir_centre_de_vacances",
            "dir_de_cabinet" => "dir_de_cabinet",
            "dirresp_affaires_culturelles" => "dirresp_affaires_culturelles",
            "dirresp_affaires_economiques" => "dirresp_affaires_economiques",
            "dirresp_affaires_sociales" => "dirresp_affaires_sociales",
            "dirresp_ccas" => "dirresp_ccas",
            "dirresp_centre_de_documentation" => "dirresp_centre_de_documentation",
            "dirresp_chsct" => "dirresp_chsct",
            "dirresp_communication" => "dirresp_communication",
            "dirresp_de_lurbanisme" => "dirresp_de_lurbanisme",
            "dirresp_de_la_logistique" => "dirresp_de_la_logistique",
            "dirresp_de_la_police" => "dirresp_de_la_police",
            "dirresp_des_achats" => "dirresp_des_achats",
            "dirresp_du_comit_dentreprise" => "dirresp_du_comit_dentreprise",
            "dirresp_eaux_et_sources" => "dirresp_eaux_et_sources",
            "dirresp_education" => "dirresp_education",
            "dirresp_emploi" => "dirresp_emploi",
            "dirresp_environnement" => "dirresp_environnement",
            "dirresp_etat_civil" => "dirresp_etat_civil",
            "dirresp_famille" => "dirresp_famille",
            "dirresp_feux_de_forets" => "dirresp_feux_de_forets",
            "dirresp_formation" => "dirresp_formation",
            "dirresp_hmatovigilance" => "dirresp_hmatovigilance",
            "dirresp_hyginequalit" => "dirresp_hyginequalit",
            "dirresp_insertion" => "dirresp_insertion",
            "dirresp_jeunesse__sport" => "dirresp_jeunesse__sport",
            "dirresp_logement" => "dirresp_logement",
            "dirresp_marchs_publics" =>"dirresp_marchs_publics",
            "dirresp_matriovigilance" =>"dirresp_matriovigilance",
            "dirresp_parcs_jardins" => "dirresp_parcs_jardins",
            "dirresp_personnes_ages" =>"dirresp_personnes_ages",
            "dirresp_personnes_handicapes" =>"dirresp_personnes_handicapes",
            "dirresp_petite_enfance" =>"dirresp_petite_enfance",
            "dirresp_pharmacovigilance" =>"dirresp_pharmacovigilance",
            "dirresp_santprvention" =>"dirresp_santprvention",
            "dirresp_services_gnraux" =>"dirresp_services_gnraux",
            "dirresp_vigilance" =>"dirresp_vigilance",
            "dirresp_ville_intgration" =>"dirresp_ville_intgration",
            "dirresp_voirie" =>"dirresp_voirie",
            "directeur" =>"directeur",
            "directeur_dtablissement" =>"directeur_dtablissement",
            "directeur_detablissement" =>"directeur_detablissement",
            "directeur_de_cabinet" =>"directeur_de_cabinet",
            "directeur_gnral_adjoint" =>"directeur_gnral_adjoint",
            "drh" =>"drh",
            "dst_dirresp_services_techniques" =>"dst_dirresp_services_techniques",
            "econome" =>"econome",
            "educateur__animateur_socio_ducatif" =>"educateur__animateur_socio_ducatif",
            "eluadjoint__lurbanisme" =>"eluadjoint__lurbanisme",
            "eluadjoint_affaires_culturelles" =>"eluadjoint_affaires_culturelles",
            "eluadjoint_affaires_economiques" =>"eluadjoint_affaires_economiques",
            "eluadjoint_affaires_juridiques" =>"eluadjoint_affaires_juridiques",
            "eluadjoint_affaires_sociales" =>"eluadjoint_affaires_sociales",
            "eluadjoint_aux_handicapes" =>"eluadjoint_aux_handicapes",
            "eluadjoint_communication" =>"eluadjoint_communication",
            "eluadjoint_education" =>"eluadjoint_education",
            "eluadjoint_emploi" =>"eluadjoint_emploi",
            "eluadjoint_environnement" =>"eluadjoint_environnement",
            "eluadjoint_famille" =>"eluadjoint_famille",
            "eluadjoint_finances" =>"eluadjoint_finances",
            "eluadjoint_insertion" =>"eluadjoint_insertion",
            "eluadjoint_jeunesse__sport" =>"eluadjoint_jeunesse__sport",
            "eluadjoint_logement" =>"eluadjoint_logement",
            "eluadjoint_marchs_publics" =>"eluadjoint_marchs_publics",
            "eluadjoint_personnes_ages" =>"eluadjoint_personnes_ages",
            "eluadjoint_petite_enfance" =>"eluadjoint_petite_enfance",
            "eluadjoint_santprvention" =>"eluadjoint_santprvention",
            "eluadjoint_services_techniques" =>"eluadjoint_services_techniques",
            "eluadjoint_urbanisme" =>"eluadjoint_urbanisme",
            "eluadjoint_ville_intgration" =>"eluadjoint_ville_intgration",
            "eluadjoint_voirie" =>"eluadjoint_voirie",
            "gardien_de_la_paix" =>"gardien_de_la_paix",
            "grant__cogrant" =>"grant__cogrant",
            "gestionnaire" =>"gestionnaire",
            "graphiste" =>"graphiste",
            "infirmier_chef_de_servicecadre_suprieur_de_sant" =>"infirmier_chef_de_servicecadre_suprieur_de_sant",
            "infirmier_coordonateur" =>"infirmier_coordonateur",
            "infirmiere_gnrale" =>"infirmiere_gnrale",
            "infirmire_scolaire" =>"infirmire_scolaire",
            "inspecteur_acadmique" =>"inspecteur_acadmique",
            "intendant" =>"intendant",
            "juge_pour_enfant" =>"juge_pour_enfant",
            "juriste" =>"juriste",
            "maire" =>"maire",
            "maitre_de_recherche" =>"maitre_de_recherche",
            "mdecin" =>"mdecin",
            "mdecin_chef_de_serviceresponsable_de_ple" =>"mdecin_chef_de_serviceresponsable_de_ple",
            "mdecin_coordonateur" =>"mdecin_coordonateur",
            "mdecin_de_pmi" =>"mdecin_de_pmi",
            "mdecin_du_travail" =>"mdecin_du_travail",
            "mdecin_praticien_hospitalier" =>"mdecin_praticien_hospitalier",
            "mdiateur_familial" =>"mdiateur_familial",
            "non_renseign" =>"non_renseign",
            "orthophoniste" =>"orthophoniste",
            "pdg_prsident_directeur_gnral" =>"pdg_prsident_directeur_gnral",
            "pdopsychiatre" =>"pdopsychiatre",
            "pdopsychiatre__psychologue" =>"pdopsychiatre__psychologue",
            "pompiers" =>"pompiers",
            "prfet" =>"prfet",
            "prsident" =>"prsident",
            "prsident_de_club" =>"prsident_de_club",
            "principal_collge" =>"principal_collge",
            "principal_adjoint" =>"principal_adjoint",
            "professeur" =>"professeur",
            "professeur_duniversit" =>"professeur_duniversit",
            "proviseur_lyces" =>"proviseur_lyces",
            "proviseur_adjoint" =>"proviseur_adjoint",
            "psychologue" =>"psychologue",
            "psychologue_scolaire" =>"psychologue_scolaire",
            "psychomotricien" =>"psychomotricien",
            "psychothrapeute" =>"psychothrapeute",
            "puriculteur__auxiliaire_de_puriculture" =>"puriculteur__auxiliaire_de_puriculture",
            "recteur" =>"recteur",
            "rfrent_ase" =>"rfrent_ase",
            "rfrent_service_enfance_famille" =>"rfrent_service_enfance_famille",
            "resp_affaires_conomiques" =>"resp_affaires_conomiques",
            "resp_comptabilit_gestion" =>"resp_comptabilit_gestion",
            "resp_service_juridique" =>"resp_service_juridique",
            "respgestionnaire_du_personnel" =>"respgestionnaire_du_personnel",
            "respgestionnaire_paie" =>"respgestionnaire_paie",
            "responsable_cdi" =>"responsable_cdi",
            "responsable_protection_enfance" =>"responsable_protection_enfance",
            "responsbale_bureau_des_lections" =>"responsbale_bureau_des_lections",
            "secrtaire__assistant" => "secrtaire__assistant",
            "secrtaire_de_mairie" =>"secrtaire_de_mairie",
            "secrtaire_gnral" =>"secrtaire_gnral",
            "secrtaire_mdicale" =>"secrtaire_mdicale",
            "secrtaireassistant" =>"secrtaireassistant",
            "surveillante_gnrale" =>"surveillante_gnrale",
            "travailleur_social" =>"travailleur_social",

            ////////////////////// TI            
            "animateur_sst_correspondant__scurit" => "animateur_sst_correspondant__scurit",
            "architecte_matre_duvre" => "architecte_matre_duvre",
            "assistant_qhse" =>"assistant_qhse",
            "AssistantResponsableFormation" =>"AssistantResponsableFormation",
            "AssistantRH"    =>"AssistantRH",
            "bureau_dtude_technique_metreur" =>"bureau_dtude_technique_metreur",
            "charg_de_mission_charg_dtude" =>"charg_de_mission_charg_dtude",
            "chef_de_chantier_opc" =>"chef_de_chantier_opc",
            "chef_de_projet_reach__clp__frd" =>"chef_de_projet_reach__clp__frd",
            "chef_des_ventes" =>"chef_des_ventes",
            "conducteur_de_travaux" =>"conducteur_de_travaux",
            "consultant_expert_formateur" =>"consultant_expert_formateur",
            "directeur_achat" =>"directeur_achat",
            "DirecteurAdministratifFinancier" =>"DirecteurAdministratifFinancier",
            "directeur_bureau_dtude_rd" =>"directeur_bureau_dtude_rd",
            "directeur_centre_de_doc_bibliothque" =>"directeur_centre_de_doc_bibliothque",
            "directeur_conditionnement" =>"directeur_conditionnement",
            "directeur_dusine" =>"directeur_dusine",
            "directeur_de_production" =>"directeur_de_production",
            "directeur_de_site" =>"directeur_de_site",
            "DirecteurRessourcesHumaines" =>"DirecteurRessourcesHumaines",
            "directeur_des_services_techniques" =>"directeur_des_services_techniques",
            "directeur_des_si" =>"directeur_des_si",
            "directeur_dveloppement" =>"directeur_dveloppement",
            "directeur_environnement" =>"directeur_environnement",
            "DirecteurFormation" =>"DirecteurFormation",
            "directeur_industriel" =>"directeur_industriel",
            "directeur_informatique" =>"directeur_informatique",
            "directeur_logistique" =>"directeur_logistique",
            "directeur_maintenance" =>"directeur_maintenance",
            "directeur_mtrologie" =>"directeur_mtrologie",
            "directeur_qualit" =>"directeur_qualit",
            "directeur_rd" => "directeur_rd",
            "directeur_technique" =>"directeur_technique",
            "conomiste_de_la_construction" =>"conomiste_de_la_construction",
            "enseignant_chercheur_matre_de_confrence" =>"enseignant_chercheur_matre_de_confrence",
            "gestionnaire_de_patrimoine_immobilier" =>"gestionnaire_de_patrimoine_immobilier",
            "infirmire" =>"infirmire",
            "ingnieur" =>"ingnieur",
            "ingnieur_commercial_sav" =>"ingnieur_commercial_sav",
            "ingenieur_conseil" =>"ingenieur_conseil",
            "ingnieur_qhse_ingnieur_environnement" =>"ingnieur_qhse_ingnieur_environnement",
            "ingnieur_territorial" =>"ingnieur_territorial",
            "inspecteur_technique" =>"inspecteur_technique",
            "iprp_hygieniste" =>"iprp_hygieniste",
            "responsable_achat" =>"responsable_achat",
            "responsable_affaires_rglementaires" =>"responsable_affaires_rglementaires",
            "responsable_be_mthode" =>"responsable_be_mthode",
            "responsable_de_laboratoire" =>"responsable_de_laboratoire",
            "responsable_de_production" =>"responsable_de_production",
            "ResponsableRessourcesHumaines" =>"ResponsableRessourcesHumaines",
            "responsable_environnement" =>"responsable_environnement",
            "ResponsableFormation" =>"ResponsableFormation",
            "responsable_hse_chef_de_service_scurit" =>"responsable_hse_chef_de_service_scurit",
            "responsable_informatique" =>"responsable_informatique",
            "responsable_logistique__magasin" =>"responsable_logistique__magasin",
            "responsable_maintenance" =>"responsable_maintenance",
            "responsable_maintenance_travaux_neufs_services_gnraux" =>"responsable_maintenance_travaux_neufs_services_gnraux",
            "responsable_marketing" =>"responsable_marketing",
            "responsable_mthode" =>"responsable_mthode",
            "responsable_phytosanitaire" =>"responsable_phytosanitaire",
            "responsable_qualit" =>"responsable_qualit",
            "responsable_rd" =>"responsable_rd",
            "responsable_risque_produit_homologation" =>"responsable_risque_produit_homologation",
            "responsable_systme_dinformation_logiciel" =>"responsable_systme_dinformation_logiciel",
            "responsable_technique" =>"responsable_technique",
            "responsable_urbanisme" =>"responsable_urbanisme",
            "SecretaireGeneral" =>"SecretaireGeneral",
            "technicien_de_laboratoire_technicien_rd" =>"technicien_de_laboratoire_technicien_rd",
            "technicien_hse" =>"technicien_hse",
            "technicien_oprateur" =>"technicien_oprateur",
            "toxicologue" =>"toxicologue"

        );

        if (array_key_exists("fonction",$data) && $data["fonction"]
            && array_key_exists($data["fonction"], $fonctions)) {
            return $fonctions[trim($data["fonction"])];
        }
        return "";
    }


}