<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Classic extends BaseMapping
{
	public function getCpwOriIDCode($data)
	{
		return 'CLASSIC';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'CLASSIC';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}
}
