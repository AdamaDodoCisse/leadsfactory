<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;

use Weka\LeadsExportBundle\Utils\Edeal\WekaMapping;


class WekaCommerciauxWebconf extends WekaMapping
{
    public function getEntCorpName($data)
    {
	    if(isset($data['type-etablissement']))
		    return $data['type-etablissement'] . ' - ' . $data['zip'];
	    return 'undefined';
    }

    public function getCpwComment($data)
    {
        $comment = 'Créé depuis l\'espace "mon compte"';
        return $comment;
    }

    public function getCpwOriIDCode($data)
    {
        return 'WEBINAR';
    }

	public function getCpwTypeDemande_($data)
	{
		return 'WEBINAR';
	}

    public function getCpwStatus_Code($data)
    {
        return 'DIATRAITER';
    }

	public function getCpwCorpName($data)
	{
		return $this->getEntCorpName($data);
	}
}
