<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;


use Doctrine\ORM\EntityManager;

class AbstractMapping {

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
            "entCorpName"	=> 'etablissement',
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
        );
    }

    public function getCouponsWebMapping()
    {
        return array(
            'cpwActIDCode'      => '',
            'cpwAdresse1'       => 'address',
            'cpwAdresse2'       => '',
            'cpwAutresCin'      => '',
            'cpwCinTmp_'        => '',
            'cpwCity'           => 'ville',
            'cpwCivilite'       => 'salutation',
            'cpwCodeGCM'        => '',
            'cpwComment'        => '',
            'cpwCorpName'       => 'etablissement',
            'cpwDate'           => '',
            'cpwDejaClient'     => '',
            'cpwDemandeRV'      => '',
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
            'cpwStopMailETI'    => '',
            'cpwStopPartenaires'=> '',
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
	        'cpwTypeDemande_'   => '',

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
		$listId = $this->em->getRepository('TellawLeadsFactoryBundle:ReferenceList')->findOneByCode('type_etablissement')->getId();
		$label = $this->em->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->getLabel($listId, $value);

		return $label;
	}

    /*
     * $couponsWeb->cpwActIDCode = '';
            $couponsWeb->cpwAdresse1 = '';
            $couponsWeb->cpwAdresse2 = '';
            $couponsWeb->cpwAutresCin = '';
            $couponsWeb->cpwCinTmp_ = '';
            $couponsWeb->cpwCity = '';
            $couponsWeb->cpwCivilite = '';
            $couponsWeb->cpwCodeGCM = '';
            $couponsWeb->cpwComment = '';
            $couponsWeb->cpwCorpName = '';
            $couponsWeb->cpwDate = '';
            $couponsWeb->cpwDejaClient = '';
            $couponsWeb->cpwDemandeRV = '';
            $couponsWeb->cpwEmail = '';
            $couponsWeb->cpwEmailValide = '';
            $couponsWeb->cpwEntIDPhone = '';
            $couponsWeb->cpwEventIDCode = '';
            $couponsWeb->cpwFonctionLabel = '';
            $couponsWeb->cpwMbm = '';
            $couponsWeb->cpwNom = '';
            $couponsWeb->cpwOriDossier = '';
            $couponsWeb->cpwOriIDCode = '';
            $couponsWeb->cpwOrigine = '';
            $couponsWeb->cpwPaysCode = '';
            $couponsWeb->cpwPerIDMail = '';
            $couponsWeb->cpwPhone = '';
            $couponsWeb->cpwPrenom = '';
            $couponsWeb->cpwProfilAutre = '';
            $couponsWeb->cpwProfilCode = '';
            $couponsWeb->cpwStatus_Code = '';
            $couponsWeb->cpwStatut = '';
            $couponsWeb->cpwStopMailETI = '';
            $couponsWeb->cpwStopPartenaires = '';
            $couponsWeb->cpwTitre = '';
            $couponsWeb->cpwTypePourImport_ = '';
            $couponsWeb->cpwUtmCampaign = '';
            $couponsWeb->cpwUtmContent = '';
            $couponsWeb->cpwUtmMedium = '';
            $couponsWeb->cpwUtmSource = '';
            $couponsWeb->cpwUtmTerm = '';
            $couponsWeb->cpwZip = '';
     */

} 