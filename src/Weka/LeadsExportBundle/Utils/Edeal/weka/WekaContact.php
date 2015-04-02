<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 02/04/15
 */

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;

use Weka\LeadsExportBundle\Utils\Edeal\WekaMapping;


class WekaContact extends WekaMapping
{
    public function getCpwCorpName($data)
    {
        if(isset($data['type-etablissement']))
            return $this->getTypeEtablissement($data['type-etablissement']) . ' - ' . $data['zip'];
        return 'undefined';
    }

    public function getCpwComment($data)
    {
        $comment = 'Provient du formulaire Essai Weka';

        if(!empty($data['product_name']))
            $comment .= "\nNom produit : " . $data['product_name'];

        if(!empty($data['product_sku']))
            $comment .= "\nSKU : " . $data['product_sku'];

        return $comment;
    }

    public function getCpwOriIDCode($data)
    {
        return 'CLASSIC';
    }

    public function getCpwActIDCode($data)
    {
        return '';
    }

    public function getCpwTypeDemande_($data)
    {
        return 'Contact';
    }

    public function getCpwSku_($data)
    {
        return $data['product_sku'];
    }

    public function getCpwProductTitle_($data)
    {
        return $data['product_name'];
    }

    public function getEntCorpName($data)
    {
        if(isset($data['type-etablissement']))
            return $this->getTypeEtablissement($data['type-etablissement']) . ' - ' . $data['zip'];
        return 'undefined';
    }

    public function getCpwProfilCode($data)
    {
        return $data['profil'];
    }
}
