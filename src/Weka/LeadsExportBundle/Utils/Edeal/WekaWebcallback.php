<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;

class WekaExtract extends AbstractMapping {



	public function getCpwOriIDCode($data)
	{
		return 'WEBCALLBACK';
	}

	public function getCpwActIDCode($data)
	{
		return 'tmk';
	}

}