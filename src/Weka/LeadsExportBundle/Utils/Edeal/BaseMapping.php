<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;


use Doctrine\ORM\EntityManager;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElementRepository;

class BaseMapping
{
	protected $em;

    /** @var ReferenceListElementRepository $list_element_repository */
    protected $list_element_repository;

	public function __construct(EntityManager $entityManager, ReferenceListElementRepository $list_element_repository)
	{
		$this->em = $entityManager;
        $this->list_element_repository = $list_element_repository;
	}

    public function getEnterpriseMapping()
    {
        return array(
            "entAd1"		=> "address",
            "entCity"       => "ville",
            "entCorpName"	=> "etablissement",
            "entCtrCode"    => 'pays',
            "entPhone"      => "phone",
            "entZip"		=> "zip",
        );
    }

    public function getPersonMapping()
    {
        return array(
            'perCity'           => 'ville',
            'perCivilite'       => 'salutation',
            'perCtrCode'        => 'pays',
            'perFstName'        => 'firstName',
            'perMail'           => 'email',
            'perName'           => 'lastName',
            'perPhone'          => 'phone',
            'perServiceCode'    => 'service',
            'perZip'            => 'zip',
	        'PerProfil'         => 'profil',
	        'perFctCode'        => 'fonction'
        );
    }

    public function getCouponsWebMapping()
    {
        return array(
            'cpwActIDCode'      => 'acteur',
            'cpwAdresse1'       => 'address',
            'cpwAdresse2'       => '',
            'cpwAutresCin'      => '',
            'cpwCin'            => 'secteur-activite',
            'cpwCity'           => 'ville',
            'cpwCivilite'       => 'salutation',
            'cpwCodeGCM'        => '',
            'cpwComment'        => 'comment',
            'cpwCorpName'       => 'etablissement',
            'cpwDate'           => '',
            'cpwDejaClient'     => 'deja-client',
            'cpwDemandeRV'      => 'demande-rdv',
            'cpwEmail'          => 'email',
            'cpwEmailValide'    => '',
            'cpwEntIDPhone'     => 'phone',
            'cpwEventIDCode'    => '',
            'cpwFonctionLabel'  => 'fonction',
            'cpwMbm'            => '',
            'cpwNom'            => 'lastName',
            'cpwOriDossier'     => 'thematique',
            'cpwOriIDCode'      => '',
            'cpwOrigine'        => '',
            'cpwPaysCode'       => 'pays',
            'cpwPerIDMail'      => 'email',
            'cpwPhone'          => 'phone',
            'cpwPrenom'         => 'firstName',
            'cpwProfilAutre'    => '',
            'cpwProfilCode'     => 'profil',
            'cpwStatus_Code'    => '',
            'cpwStatut'         => '',
            'cpwStopMailETI'    => 'cnilTi',
            'cpwStopPartenaires'=> 'cnilPartners',
            'cpwTitre'          => '',
            'cpwTypePourImport_'=> '',
            'cpwUtmCampaign'    => 'utmcampaign',
            'cpwUtmContent'     => 'utmcontent',
            'cpwUtmMedium'      => 'utmmedium',
            'cpwUtmSource'      => 'utmsource',
            'cpwUtmTerm'        => '',
            'cpwZip'            => 'zip',
	        'cpwTypeDemande_'   => '',
	        'cpwSku_'           => 'product_sku',
	        'cpwProductTitle_'  => 'product_name',
        );
    }

    public function getCpwOriIDCode($data)
    {
        return 'CLASSIC';
    }

    public function getCpwDate($data)
    {
        return date('m/d/Y H:i');
    }

    public function getCpwStatus_Code($data)
    {
        return 'DIATRAITER';
    }

	protected function getTypeEtablissement($value)
	{
		$label = $this->list_element_repository->getNameUsingListCode('type_etablissement', $value);

		return $label;
	}

	public function getCpwCin($data)
	{
		if(!empty($data['secteur-activite'])){
			return $this->list_element_repository->getNameUsingListCode('ti_secteur_activite', $data['secteur-activite']);
		}else{
			return null;
		}
	}

	public function getBooleanString($value)
	{
		return ($value === '1')?'true':'false';
	}

	public function getCpwDemandeRV($data)
	{
		return isset($data['demande-rdv']) ? $this->getBooleanString($data['demande-rdv']) : null;
	}

	public function getCpwDejaClient($data)
	{
		return isset($data['deja-client']) ? $this->getBooleanString($data['deja-client']) : null;
	}

	public function getCpwStopMailETI($data)
	{
		return isset($data['cnilTi']) ? $this->getBooleanString($data['cnilTi']) : null;
	}

	public function getCpwStopPartenaires($data)
	{
		return isset($data['cnilPartners']) ? $this->getBooleanString($data['cnilPartners']) : null;
	}

	public function getEntCity($data)
	{
		if(empty($data['ville_id']) && empty($data['ville_text'])){
			return null;
		}else {
			return ! empty( $data['ville_id'] ) ? $data['ville_id'] : $data['ville_text'];
		}
	}

	public function getPerCity($data)
	{
		return $this->getEntCity($data);
	}

	public function getCpwCity($data)
	{
		return $this->getEntCity($data);
	}

	public function getEntCorpName($data)
	{
		return !empty($data['etablissement']) ? $data['etablissement'] : 'Non renseigné';
	}

	public function getCpwCorpName($data)
	{
		return $this->getEntCorpName($data);
	}

	public function getEntPhone($data)
	{
		return !empty($data['phone']) ? $data['phone'] : 'Non renseigné';
	}

	public function getPerMail($data)
	{
		return !empty($data['email']) ? $data['email'] : 'Non renseigné';
	}


}
