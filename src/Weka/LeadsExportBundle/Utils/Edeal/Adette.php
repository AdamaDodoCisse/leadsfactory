<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;


class Adette {

    public function getEnterpriseMapping()
    {
        return array(
            "entAd1"		=> "address",
            "entCity"       => "ville",
            //"entCorpName"	=> "",
            //"entCtrCode"  => "",
            "entPhone"      => "phone",
            "entZip"		=> "zip",
        );
    }

    public function getPersonMapping()
    {
        return array(
            //'perCity'           => '',
            'perCivilite'       => 'salutation',
            'perCtrCode'        => '',
            'perFstName'        => 'firstName',
            'perMail'           => 'email',
            'perName'           => 'lastName',
            'perPhone'          => 'phone',
            'perServiceCode'    => 'service',
            //'perZip'            => '',
        );
    }

    public function getPerCtrCode($data)
    {
        return 'FR';
    }

    public function getCouponsWebMapping()
    {
        return array(
            'cpwAdresse1'       => 'address',
            'cpwCity'           => 'ville',
            'cpwCivilite'       => 'salutation',
            'cpwCorpName'       => 'lastName',
            'cpwEmail'          => 'email',
            'cpwPhone'          => 'phone',
            'cpwPrenom'         => 'firstName',
            'cpwNom'            => 'lastName',
            'cpwUtmCampaign'    => 'utmcampaign',
            'cpwZip'            => 'zip',
            'cpwPaysCode'       => '',
            'cpwOriid'          => ''
        );
    }

    public function getCpwPaysCode($data)
    {
        return 'FR';
    }

    public function getCpwOriid($data)
    {
        return 'WEBCALLBACK';
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