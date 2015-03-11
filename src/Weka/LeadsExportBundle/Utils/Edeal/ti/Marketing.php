<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Marketing extends BaseMapping
{
	public function getCpwOriIDCode($data)
	{
		return 'CLASSIC';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwSku_($data)
	{
		return null;
	}

	public function getCpwProductTitle_($data)
	{
		return null;
	}
}
