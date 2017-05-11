<?php
namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Webcallback extends BaseMapping
{
	public function getCpwOriIDCode($data)
	{
		return 'WEBCALLBACK';
	}

	public function getCpwActIDCode($data)
	{
		return 'TMK';
	}

	public function getEntZip($data)
	{
		return '00000';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'WEBCALLBACK';
	}
}
