<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Extrait extends BaseMapping{

	public function getCpwOriIDCode($data)
	{
		return 'EXTRAIT';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'EXTRAIT';
	}
}
