<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2\ti;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class TICallback extends AthenaV2BaseMapping {

    public function getType_demande ( $data ) {
        return "ti_webcallback";
    }


    public function getDetail_demande($data){
        $comment =  "Une demande Webcallback a été faite par : ";
        $comment .= strtoupper($data['firstName']) . " " . $data['lastName'];

        if (array_key_exists("etablissement",$data) && $data["etablissement"]) {
            $comment .= ", de la société " . strtoupper($data['etablissement']) ;
        }

        if (array_key_exists("phone",$data) && $data["phone"]) {
            $telephone = '';
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
            }
            $comment .= ", telephone : " . strtoupper($telephone);
        }

        if (array_key_exists("product_name",$data) && $data["product_name"]) {
            $comment .= ". A propos du produit : " . $data["product_name"];

            if (array_key_exists("comment",$data) && $data["comment"]) {
                $comment .= ". Commentaire : ".$data["comment"] . ".";
            }
        }
        return $comment;
    }
}