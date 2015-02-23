<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class Devispack extends BaseMapping{

	public function getCpwOriIDCode($data)
	{
		return 'CLASSIC';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwComment($data)
	{
		$comment = "Demande d'information sur le pack ";

		if(!empty($data['product_name']))
			$comment .= $data['product_name'];

		if(!empty($data['product_sku']))
			$comment .= "\nSKU : " . $data['product_sku'];

		$comment = 'Effectuée le '.date('c');

		return $comment;
	}

	public function getCpwOriDossier($data)
	{
		return !empty($data['product_sku']) ? $data['product_sku'] : '';
	}

	public function getEntCity($data)
	{
		return !empty($data['ville_id']) ? $data['ville_id'] : $data['ville_text'];
	}

	public function getPerCity($data)
	{
		return $this->getEntCity($data);
	}

	public function getCpwCity($data)
	{
		return $this->getEntCity($data);
	}

}