<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;

use Weka\LeadsExportBundle\Utils\Edeal\WekaMapping;


class Webinar extends WekaMapping
{
    public function getEntCorpName($data)
    {
	    if(isset($data['type-etablissement']))
		    return $data['type-etablissement'] . ' - ' . $data['zip'];
	    return 'undefined';
    }

    public function getCpwComment($data)
    {
        $comment = 'Provient du formulaire WEBINAR';

        if(isset($data['type-etablissement']))
            $comment .= "\nType d'établissement : ".$data['type-etablissement'];

        return $comment;
    }

    public function getCpwOriIDCode($data)
    {
        return 'WEBINAR';
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
