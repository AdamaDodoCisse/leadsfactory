<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;


class AbstractMapping {

    public function getEnterpriseMapping()
    {
        return array(
            "entAd1"		=> "address",
            "entCity"       => "ville",
            "entCorpName"	=> '',
            "entCtrCode"    => "",
            "entPhone"      => "phone",
            "entZip"		=> "zip",
        );
    }

    public function getEntCorpName($data)
    {
	    if(isset($data['type-etablissement']))
		    return $data['type-etablissement'] . ' - ' . $data['zip'];
	    return 'undefined';
    }

	public function getEntCtrCode($data)
	{
		return 'FR';
	}

    public function getPersonMapping()
    {
        return array(
            'perCity'           => 'ville',
            'perCivilite'       => 'salutation',
            'perCtrCode'        => '',
            'perFstName'        => 'firstName',
            'perMail'           => 'email',
            'perName'           => 'lastName',
            'perPhone'          => 'phone',
            'perServiceCode'    => 'service',
            'perZip'            => 'zip',
        );
    }

    public function getPerCtrCode($data)
    {
        return 'FR';
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
            'cpwCorpName'       => '',
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
            'cpwPaysCode'       => '',
            'cpwPerIDMail'      => '',
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
	        'cpwSku_'           => '',
	        'cpwProductTitle_'  => ''

        );
    }

	public function getCpwComment($data)
    {
        return '';
    }

    public function getCpwPaysCode($data)
    {
        return 'FR';
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

	public function getCpwCorpName($data)
	{
		if(isset($data['type-etablissement']))
			return $data['type-etablissement'] . ' - ' . $data['zip'];
		return 'undefined';
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