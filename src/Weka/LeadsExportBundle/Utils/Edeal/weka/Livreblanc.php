<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Livreblanc extends BaseMapping {

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

    public function getPerCtrCode($data)
    {
        return 'FR';
    }

    public function getCpwComment($data)
    {
        $comment = 'Provient du formulaire Télchargement de livre blanc (actu)';

        if(isset($data['type-etablissement']))
            $comment .= "\nType d'établissement : ".$this->getTypeEtablissement($data['type-etablissement']);

        if(isset($data['livre-blanc']))
            $comment .= "\nLivre blanc  : ".$data['livre-blanc'];

        return $comment;
    }

    public function getCpwPaysCode($data)
    {
        return 'FR';
    }

    public function getCpwOriIDCode($data)
    {
        return 'CLASSIC';
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
			return $this->getTypeEtablissement($data['type-etablissement']) . ' - ' . $data['zip'];
		return 'undefined';
	}
} 