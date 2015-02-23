<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;


use Doctrine\ORM\EntityManager;

class BaseMapping
{
	protected $em;

	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
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
            'cpwActIDCode'      => '',
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
            'cpwOriDossier'     => '',
            'cpwOriIDCode'      => '',
            'cpwOrigine'        => '',
            'cpwPaysCode'       => 'pays',
            'cpwPerIDMail'      => 'email',
            'cpwPhone'          => 'phone',
            'cpwPrenom'         => 'firstName',
            'cpwProfilAutre'    => '',
            'cpwProfilCode'     => '',
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

	public function getCpwComment($data)
    {
        return '';
    }

    public function getCpwOriIDCode($data)
    {
        return 'CLASSIC';
    }

    public function getCpwDate($data)
    {
        return date('m/d/Y');
    }

    public function getCpwStatus_Code($data)
    {
        return 'DIATRAITER';
    }

	protected function getTypeEtablissement($value)
	{
		$label = $this->em->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->getNameUsingListCode('type_etablissement', $value);

		return $label;
	}

	public function getCpwCin($data)
	{
		$repository = $this->em->getRepository('TellawLeadsFactoryBundle:ReferenceListElement');
		return $repository->getNameUsingListCode('ti_secteur_activite', $data['secteur-activite']);
	}

	public function getBooleanString($value)
	{
		return ($value === '1')?'true':'false';
	}
}
