<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Livreblanc extends BaseMapping{

	public function getCpwOriIDCode($data)
	{
		return 'WHITE-PAPER';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'WHITE-PAPER';
	}

	public function getCpwTypePourImport_($data)
	{
		return 'WPWEB';
	}

	public function getDetail_demande ($data) {

		$comment = 'Provient du formulaire Téléchargement de livre blanc (actu)';

		if(isset($data['type-etablissement']))
			$comment .= "\nType d'établissement : ".$this->getTypeEtablissement($data['type-etablissement']);

		if(isset($data['livre-blanc']))
			$comment .= "\nLivre blanc  : ".$data['livre-blanc'];

		return $comment;

	}

}
