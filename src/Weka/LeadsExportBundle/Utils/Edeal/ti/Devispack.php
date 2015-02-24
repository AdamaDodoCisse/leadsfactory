<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Devispack extends BaseMapping{

	public function getCpwOriIDCode($data)
	{
		return 'CLASSIC';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'PACK';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwComment($data)
	{
		return '';
	}

	public function getCpwDemandeRV($data)
	{
		return 'true';
	}
}
