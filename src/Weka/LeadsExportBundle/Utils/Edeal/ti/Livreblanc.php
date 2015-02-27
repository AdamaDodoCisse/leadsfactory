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
}
