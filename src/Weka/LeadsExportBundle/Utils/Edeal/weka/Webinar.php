<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;


class Webinar {

    public function getEnterpriseMapping()
    {
        return array(
            "entAd1"		=> "",
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
            'cpwAdresse1'       => '',
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
            'cpwUtmContent'     => '',
            'cpwUtmMedium'      => '',
            'cpwUtmSource'      => '',
            'cpwUtmTerm'        => '',
            'cpwZip'            => 'zip',

        );
    }

    public function getCpwComment($data)
    {
        $comment = 'Provient du formulaire WEBINAR';

        if(isset($data['type-etablissement']))
            $comment .= "\nType d'établissement : ".$data['type-etablissement'];

        return $comment;
    }

    public function getCpwPaysCode($data)
    {
        return 'FR';
    }

    public function getCpwOriIDCode($data)
    {
        return 'WEBINAR';
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

	public function getCpwTypeDemande_($data)
	{
		return 'WEBINAR';
	}

} 