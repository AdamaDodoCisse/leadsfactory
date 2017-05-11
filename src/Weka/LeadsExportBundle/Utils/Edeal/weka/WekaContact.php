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
    public function getCpwCorpName($data){
	    if($data['profil'] == 'PARTICULIER'){
		    return 'Particulier';
	    }elseif($data['type-etablissement']) {
		    return $this->getTypeEtablissement( $data['type-etablissement'] ) . ' - ' . $data['zip'];
	    }else {
		    return 'undefined';
	    }
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
        return 'CONTACT';
    }

    public function getEntCorpName($data)
    {
        return $this->getCpwCorpName($data);
    }
}
