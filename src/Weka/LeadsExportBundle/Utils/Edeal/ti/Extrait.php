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
		return in_array($data['pays'], array('FR', 'BE', 'LU', 'CH', 'MC')) ? 'TMK' : 'FMI';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'EXTRAIT';
	}

	/*public function getCpwTypePourImport_($data)
	{
		return 'EGWEB';
	}*/
}
