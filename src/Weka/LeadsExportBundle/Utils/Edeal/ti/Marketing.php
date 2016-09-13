<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Marketing extends BaseMapping
{
	public function getCpwOriIDCode($data)
	{
		return 'MKG';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'MKG';
	}

	public function getCpwSku_($data)
	{
		return null;
	}

	public function getCpwProductTitle_($data)
	{
		return null;
	}

	public function getCpwActIDCode($data)
	{
		if($data['acteur'] == 'TMK' && !in_array($data['pays'], array('FR', 'BE', 'LU', 'CH', 'MC'))){
			return 'FMI';
		}else{
			return $data['acteur'];
		}
	}
}
