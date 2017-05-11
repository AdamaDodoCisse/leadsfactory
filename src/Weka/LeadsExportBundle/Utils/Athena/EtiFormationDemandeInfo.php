<?php

namespace Weka\LeadsExportBundle\Utils\Athena;

//use Weka\LeadsExportBundle\Utils\Athena\AbstractMapping;

class EtiFormationDemandeInfo extends AbstractMapping{

    const SOURCE = 'ti_demande_info_formation';
    const ATHENA_REQUEST_KEY = 'last_name';
    const ATHENA_PRIVATE_KEY = '2ju_9n1uwao';

    public function getSource()
    {
        return self::SOURCE;
    }

    public function getAthenaRequestKey()
    {
        return self::ATHENA_REQUEST_KEY;
    }

    public function getAthenaPrivateKey()
    {
        return self::ATHENA_PRIVATE_KEY;
    }

    public function getMappingArray()
    {
        return array(
            "ref"			=> "product",
            "company"       => "account",
            "salutation"	=> "salutation",
            "lastname"  	=> "last_name",
            "firstname"     => "first_name",
            "fonction"		=> "title",
            "phone"      	=> "phone",
            "email" 		=> "emails",
            "comment" 		=> "comments",
            "campaign"	    => "campaign",
            //"date_location"	=> "location",
            //"date_start"	=> "mission_date",
            "athenaid"      => "athenaid",
            "date_creation" => "entered"

        );
    }

    public function getEntered()
    {
        return date('c');
    }

    /*public function getProduct($data)
    {
        return str_replace(' ', '', $data['ref']);
    }*/

    public function getComments ( $data )
    {
        return ($data["comment"] == "") ? "Aucun commentaire" : $data["comment"];
    }

    public function getPhone ($data)
    {
        return $this->formatPhone($data["phone"]);
    }
}