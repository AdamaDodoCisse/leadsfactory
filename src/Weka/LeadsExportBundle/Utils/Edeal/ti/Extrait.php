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
		if(in_array($data['pays'], array('FR', 'BE', 'LU', 'CH', 'MC'))){
			if(!empty($data['deja-client'])){
				$act = '';
			}else{
				$act = 'TMK';
			}
		}else{
			$act = 'FMI';
		}
		return $act;
	}

	public function getCpwTypeDemande_($data)
	{
		return 'EXTRAIT';
	}

	public function getEntPhone($data)
	{
		return !empty($data['phone']) ? $data['phone'] : 'undefined';
	}

	public function getPerPhone($data)
	{
		return $this->getEntPhone($data);
	}

	public function getCpwPhone($data)
	{
		return $this->getEntPhone($data);
	}

}
