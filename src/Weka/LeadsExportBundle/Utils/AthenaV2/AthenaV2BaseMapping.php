<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2;


use Doctrine\ORM\EntityManager;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElementRepository;

class AthenaV2BaseMapping
{

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


    public function getProduitMapping()
    {

        return array(
            "id_athena" => "", // Vide
            "athena_unique_id" => "",  // Vide ou ID Session si formation
            "code_sap_mpf" => "",  // Vide
            "code_sap_mi" => "",  // Vide
            "code_sap_offre" => "product_sku",
        );

    }

    public function getCompteMapping()
    {

        return array(
            "id_athena" => "",  // Vide
            "id_sap" => "",  // Vide (Prevoir evolution si connecte)
            "id_sogec" => "",  // Vide
            "raison_sociale" => "etablissement",
            "id_campagne" => "",  // Methode de récupération de la données
            "rue_facturation" => "address",
            "code_postal_facturation" => "zip",
            "dep_region_facturation" => "",  // Vide
            "ville_facturation" => "",  // Méthode de récupération de la données
            "nb_habitants" => "",  // Méthode de récupération de la données
            "pays_facturation" => "pays",
            "rue_livraison" => "",
            "code_postal_livraison" => "",  // Vide
            "dep_region_livraison" => "",  // Vide
            "ville_livraison" => "",  // Vide
            "pays_livraison" => "",  // Vide
            "effectif_site" => "",  // Vide
            "fax" => "",  // Vide
            "telephone" => "phone",
            "email" => "email",
            "siret" => "",  // Vide
            "naf" => "",  // Vide
            "ce" => "",  // Vide
            "secteur_activite_tissot_ti_cctp" => "",    // Methode de récuperation de la donnée
            "secteur_activite_weka" => "",  // Methode de récuperation de la donnée
            "id_web" => "",
            "site_web" => "",
            "type_compte" => "",  // Vide
            "date_prochaine_election_ce" => "", // Vide
            "nb_lits" => "",  // Vide
            "tranche_effectifs" => "",  // Vide
            "tranche_population" => "",  // Vide
            "presence_dup" => "",  // Vide
            "type_client_ec" => "",  // Vide
            "numero_tva_intra" => "",  // Vide
            "tab_contact" => "",   // Methode de récupération des données
            "stop_email" => "",  // Methode de récupération des données
            "stop_mail" => "",
            "stop_fax" => "",
            "stop_phoning" => "",
            "stop_global" => "",
            "raison_sociale1" => "",
            "raison_sociale2" => "",
//            "tranche_lits"              => "",  // Vide

        );

    }

    public function getContactMapping()
    {

        return array(
            "id_athena" => "",  // Vide
            "id_sap" => "",  // Vide
            "id_sogec" => "",  // Vide
            "membre_chsct" => "",  // Vide
            "delegue_perso" => "", // Vide
            "membre_dup" => "", // Vide
            "civilite" => "",  // Methode de récupération des données
            "id_campagne" => "",  // Methode de récupération des données
            "prenom" => "firstName",
            "nom" => "lastName",
            "fonction_marketing" => "",  // Methode de récupération des données
            "service" => "",  // Methode de récupération des données
            "telephone" => "phone",
            "portable" => "",  // Vide
            "fax" => "",  // Vide
            "email" => "email",
            "est_manager" => "",  // Vide
            "type_utilisation" => "",  // Vide
            "id_web" => "",  // Vide
            "membre_ce" => "",  // Vide
            "stop_email" => "",  // Methode de récupération des données
            "stop_mail" => "",
            "stop_fax" => "",
            "stop_phoning" => "",
            "stop_global" => "",
            "profil_ti" => "",  // Methode de récupération des données
            "profil_weka" => "",
            "email_valide" => "",   // Methode de récupération des données
            "tissot_panel_membre" => "",
            "tissot_panel_inscription" => "",
            "tissot_panel_gamme" => "",
            "tissot_panel_rdv" => "",
            "tissot_panel_appels" => "",
            "tissot_panel_groupe" => "",
            "tissot_panel_date_sollicitation" => "",
            "est_gestionnaire_formation" => "",
            "mandat_electif" => "",
            "mandat_salarial" => "",
            "missions_rh" => "",
            "missions_paie" => "",
            "missions_compta" => "",
//            "interets_ti"               => "",
//            "interets_tissot"           => "",  // Vide
//            "interets_weka"             => "",
//            "type_compte_cctp"          => "",  // Vide
//            "responsable_prescription_cctp" => "",  // Vide

        );

    }

    /**
     * Refactored 18-1 for Athena V3
     *
     * @return array
     */
    public function getDRCMapping()
    {

        $dateTime = new \DateTime();

        return array(
            "id_leadsfactory" => "id_leadsfactory",
            "detail_demande" => "",  // Vide
            "marque" => "",  // Vide
            "deja_client" => "deja-client",
            "id_sogec" => "",  // Boolean
            "id_sap" => "",  // Vide
            "periode_mission" => "",  // Vide
            "lieu_souhaite" => "",  // Vide
            "nb_participants" => "",  // Vide
            "theme_demande" => "",  // Vide
            "objectif_principal" => "",  // Vide
            "presentation_projet" => "",  // Vide
            "attente_eti" => "",  // Vide
            "rdv_conseiller" => "demande-rdv",  // Boolean /
            "id_compte" => "",  // Methode de récupération des données
            "id_contact" => "",  // Methode de récupération des données
            "type_demande" => "",  // Methode de récupération des données
            "activite" => "",  // Vide
            "id_campagne" => "",  // Methode de récupération des données
            "id_produit" => "",  // Methode de récupération des données
            "date_creation" => $dateTime->format("c"),
            "statut" => "",  // Vide
            "id_formation" => "",  // Vide
            "adapter_formation" => "",  // Vide
            "region_souhaitee_formation" => "", // Vide
            "detail_demande_formation" => "",  // Vide
            "dep_souhaite_formation" => "",  // Vide
            "contexte" => "",  // Vide
            "langue" => "",  // Vide
            "systeme_exploitation" => "",  // Vide
            "id_assignation" => "",   // Vide
            "version" => "",   // Methode de récupération des données
            "utmmedium" => "",   //
            "utmcontent" => "",   //
            "referrer_url" => "",   //
            "redirect_url" => "",   //

            // Jira : ST-5281
            "acteur" => "acteur",

            "trackin_origin" => "",   //

            // Ajout des méthodes V3
            "civilite" => "",   // methode de récupération des données
            "nom_contact" => "lastName",
            "prenom_contact" => "firstName",
            "email" => "email",
            "service" => "",  // methode de récupération des données
            "secteur_activite_weka" => "",  // methode de récupération des données
            "fonction_marketing" => "",  // methode de récupération des données
            "telephone" => "phone",
            "telephone_mobile" => "",
            "rue_facturation" => "address",
            "code_postal_facturation" => "zip",
            "ville_facturation" => "",  // methode de récupération des données
            "pays_facturation" => "pays",
            "sku_produit" => "product_sku",
            "thematique_weka" => "", // Information inconnue
            "nom_compte" => "etablissement",
        );

    }

    // #########################################################################################
    // OVERRIDE DES GETTERS
    // #########################################################################################


    public function getTelephone($data)
    {
        $telephone = "";
        if (array_key_exists("phone", $data) && $data["phone"]) {
            $telephone = $data["phone"];
            if (array_key_exists("pays", $data) && $data["pays"]) {
                switch ($data['pays']) {
                    case 'FR':
                        $telephone = '+33'.$data['phone'];
                        break;
                    case 'BE':
                        $telephone = '+32'.$data['phone'];
                        break;
                    case 'MC':
                        $telephone = '+377'.$data['phone'];
                        break;
                    case 'LU':
                        $telephone = '+352'.$data['phone'];
                        break;
                    case 'CH':
                        $telephone = '+41'.$data['phone'];
                        break;
                    default :
                        $telephone = $data['phone'];
                        break;
                }
            }
        }

        return $telephone;
    }

    public function getVille_facturation($data)
    {
        $ma_ville = null;

        if (array_key_exists('ville_id', $data) && $data['ville_id']) {
            $ma_ville = $this->list_element_repository->getNameUsingListCodeAndValue("ville", $data['ville_id']);
        } else {
            if (array_key_exists("ville", $data) && $data['ville']) {
                $ma_ville = $this->list_element_repository->getNameUsingListCodeAndValue("ville", $data['ville']);
            } else {
                if (array_key_exists("ville_text", $data) && $data['ville_text']) {
                    $ma_ville = $data['ville_text'];
                }
            }
        }

        // Data treatment
        if (is_array($ma_ville)) {
            if (count($ma_ville) < count($ma_ville, COUNT_RECURSIVE)) {
                if (array_key_exists('name', $ma_ville[0])) {
                    $ma_ville = $ma_ville[0]['name'];
                } else // If array is NOT multidimensional
                {
                    if (array_key_exists('name', $ma_ville)) {
                        $ma_ville = $ma_ville['name'];
                    }
                }
            }
        }

        return $ma_ville;
    }

    public function getNb_habitants($data)
    {

        $population = "";
        if (array_key_exists('zip', $data) && $data['zip']) {
            $first_str = substr($data['zip'], 0, 1);
            if ($first_str == 0) {
                $zip_code = substr($data['zip'], 1);
            } else {
                $zip_code = $data['zip'];
            }

            if (array_key_exists('ville_id', $data) && $data['ville_id']) {
                $population = $this->list_element_repository->getValueUsingListCodeAndName(
                    "nbhabitants",
                    $zip_code."-".$data['ville_id']
                );
            } else {
                if (array_key_exists('ville', $data) && $data['ville']) {
                    $population = $this->list_element_repository->getValueUsingListCodeAndName(
                        "nbhabitants",
                        $zip_code."-".$data['ville']
                    );
                } else {
                    if (array_key_exists('ville_text', $data) && $data['ville_text']) {
                        $ville_id = $this->list_element_repository->getValueUsingListCodeAndName(
                            "ville",
                            $zip_code."-".$data['ville_text']
                        );
                        $population = $this->list_element_repository->getValueUsingListCodeAndName(
                            "nbhabitants",
                            $ville_id
                        );
                    }
                }
            }
        }

        $pop = "";
        if (is_array($population) && count($population) && array_key_exists('value', $population[0])) {
            $pop = $population[0]['value'];
        } else {
            if (is_string($population)) {
                $pop = $population;
            }
        }

        return $pop;
    }

    public function getStop_email($data)
    {
        $Stop_email = array(
            "0" => "0",
            "1" => "1",
            "2" => "2",
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

    public function getDetail_demande($data)
    {
        $comment = "Une demande a été faite ";

        if (array_key_exists("firstName", $data) && $data["firstName"]
            && array_key_exists("lastName", $data) && $data["lastName"]
        ) {
            $comment .= "par : ".ucfirst($data['firstName'])." ".ucfirst($data['lastName']);
        }

        if (array_key_exists("etablissement", $data) && $data["etablissement"]) {
            $comment .= ", société : ".strtoupper($data['etablissement']);
        }

        $telephone = $this->getTelephone($data);
        if ($telephone) {
            $comment .= ", telephone : ".strtoupper($telephone);
        }

        if (array_key_exists("product_name", $data) && $data["product_name"]) {
            $comment .= ". A propos du produit : ".$data["product_name"];
        }

        if (array_key_exists("thematique", $data) && $data["thematique"]) {
            $comment .= ". Thematique : ".$data["thematique"];
        }

        if (array_key_exists("comment", $data) && $data["comment"]) {
            $comment .= ". Commentaire : ".$data["comment"].".";
        }

        //JIRA  : ST-5283
        if (array_key_exists("type-etablissement", $data) && $data["type-etablissement"]) {
            $comment .= "\nType d'établissement : ".$this->getSecteur_activite_weka($data);
        }

        if (array_key_exists("livre-blanc", $data) && $data["livre-blanc"]) {
            $comment .= "\nLivre blanc : ".$data['livre-blanc'];
        }

        return $comment;
    }

    public function getRdv_conseiller($data)
    {
        if (array_key_exists("demande-rdv", $data)) {
            return $data["demande-rdv"] ? true : false;
        }

        return false;
    }

    public function getVersion()
    {
        return "2.0";
    }

    public function getEmail_valide()
    {
        return true;
    }


    public function getAffaireMapping()
    {
        return array();
    }

    public function getArticleMapping()
    {
    }


    public function getSecteur_activite_weka($data)
    {

        if (array_key_exists("type-etablissement", $data)) {
            return $data["type-etablissement"];
        }

        return "";
    }


    public function getSecteur_activite_tissot_ti_cctp($data)
    {
        if (array_key_exists("secteur-activite", $data)) {
            return $data["secteur-activite"];
        }

        return "";
    }


    public function getTab_contact($data)
    {

        $d_email = array_key_exists("email", $data) ? $data["email"] : "";
        $d_nom = array_key_exists("lastName", $data) ? $data["lastName"] : "";
        $d_prenom = array_key_exists("firstName", $data) ? $data["firstName"] : "";
        $contact = array(
            "email_contact" => $d_email,
            "nom" => $d_nom,
            "prenom" => $d_prenom,
            "id_contact" => "",
        );

        return $contact;
    }

    public function getCivilite($data)
    {

        $civilite = array(
            "MR" => "m",
            "MRS" => "mme",
        );

        if (array_key_exists("salutation", $data) && $data["salutation"]
            && array_key_exists($data["salutation"], $civilite)
        ) {
            return $civilite[$data["salutation"]];
        }

        return "";
    }

    public function getProfil_ti($data)
    {

        $profil_ti = array(
            "ETUDIANT" => "ETUDIANT",
            "PROFESSIONNEL" => "PROFESSIONNEL",
            "PARTICULIER" => "PARTICULIER",
        );

        if (array_key_exists("profil", $data) && $data["profil"]
            && array_key_exists($data["profil"], $profil_ti)
        ) {
            return $profil_ti[$data["profil"]];
        }

        return "";
    }


    public function getService($data)
    {
        if (array_key_exists("service", $data)) {
            return $data["service"];
        }

        return "";
    }


    public function getFonction_marketing($data)
    {
        if (array_key_exists("fonction", $data)) {
            return $data["fonction"];
        }

        return "";
    }

}
