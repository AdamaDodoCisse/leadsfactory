<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Classic extends BaseMapping{

	public function getCpwOriIDCode($data)
	{
		return 'CLASSIC';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwComment($data)
	{
		$comment = "Demande d'information";

		return $comment;
	}

	public function getCpwDemandeRV($data)
	{
		return $this->getBooleanString($data['demande-rdv']);
	}

	public function getCpwSku_($data)
	{
		return null;
	}

	public function getCpwProductTitle_($data)
	{
		return null;
	}

	public function getCpwDejaClient($data)
	{
		return $this->getBooleanString($data['deja-client']);
	}

	public function getCpwStopMailETI($data)
	{
		return $this->getBooleanString($data['cnilTi']);
	}

	public function getCpwStopPartenaires($data)
	{
		return $this->getBooleanString($data['cnilPartners']);
	}

	public function getEntCity($data)
	{
		return !empty($data['ville_id']) ? $data['ville_id'] : $data['ville_text'];
	}

	public function getPerCity($data)
	{
		return $this->getEntCity($data);
	}

	public function getCpwCity($data)
	{
		return $this->getEntCity($data);
	}
}
