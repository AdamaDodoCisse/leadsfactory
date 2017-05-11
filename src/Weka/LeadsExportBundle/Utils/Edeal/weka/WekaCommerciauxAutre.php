<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;

use Weka\LeadsExportBundle\Utils\Edeal\WekaMapping;


class WekaCommerciauxAutre extends WekaMapping
{
    public function getEntCorpName($data)
    {
	    if(isset($data['type-etablissement']))
		    return $data['type-etablissement'] . ' - ' . $data['zip'];
	    return 'undefined';
    }

    public function getCpwOriIDCode($data)
    {
        return 'AUTRE';
    }

	public function getCpwTypeDemande_($data)
	{
		return 'AUTRE';
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
