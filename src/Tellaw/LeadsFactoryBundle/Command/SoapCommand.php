<?php
/**
 * Created by Olivier Lombard
 * @author olombard 
 * Date: 06/10/14
 */

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Component\Config\Definition\Exception\Exception;
use \Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SoapCommand extends Command
{
    protected $wsdl;
    protected $user;
    protected $password;

    public function __construct($wsdl, $user, $password)
    {
        $this->wsdl = $wsdl;
        $this->user = $user;
        $this->password = $password;

        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('leadsfactory:soap')
            ->setDescription('Uses E-Deal webservice')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client  = new \SoapClient($this->wsdl, array('soap_version' => SOAP_1_2, 'trace' => true));

        try {
            $response = $client->authenticate($this->user, $this->password);
            if ($response !== true) {
                throw new Exception('Authentication failed');
            }

            $enterprise = new \StdClass();
            $enterprise->entAd1 = '';
            $enterprise->entCity = '';
            $enterprise->entCorpName = '';
            $enterprise->entCtrCode = '';
            $enterprise->entPhone = '';
            $enterprise->entZip = '';
//            $response = $client->createEnterprise($enterprise);

            $person = new \StdClass();
            $person->perCity = '';
            $person->perCivilite = '';
            $person->perCtrCode = '';
            $person->perFstName = '';
            $person->perMail = '';
            $person->perName = '';
            $person->perPhone = '';
            $person->perServiceCode = '';
            $person->perZip = '';
//            $client->createPerson($person);

            $couponsWeb = new \StdClass();
            $couponsWeb->cpwActIDCode = '';
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
//            $client->createCouponsWeb_($couponsWeb);

            var_dump($response);
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
        }
    }
}