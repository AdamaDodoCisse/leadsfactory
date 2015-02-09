<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\AbstractMapping;

class Devispack extends AbstractMapping{

	public function getCpwOriIDCode($data)
	{
		return 'CLASSIC';
	}

	public function getCpwActIDCode($data)
	{
		return 'TMK';
	}

	public function getCpwComment($data)
	{
		$comment = "Demande d'information sur le pack";

		if(!empty($data['product_name']))
			$comment .= $data['product_name'];

		if(!empty($data['product_sku']))
			$comment .= "\nSKU : " . $data['product_sku'];

		return $comment;
	}

	public function getCpwOriDossier($data)
	{
		return !empty($data['product_sku']) ? $data['product_sku'] : '';
	}

}