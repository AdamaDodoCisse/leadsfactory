<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;

class WekaExtract extends AbstractMapping {

	public function getCpwComment($data)
	{
		$comment = 'Provient du formulaire Extrait gratuit Weka';

		if(isset($data['referrer_url']))
			$comment .= "\nURL : ".$data['referrer_url'];

		$comment .= "\nNom produit : " . $data['product_name'];
		$comment .= "\nSKU : " . $data['product_sku'];

		return $comment;
	}

	public function getCpwOriIDCode($data)
	{
		return 'EXTRACT';
	}

	public function getCpwActIDCode($data)
	{
		return 'tmk';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'Extrait';
	}

	public function getCpwSku_($data)
	{
		return isset($data['product_sku']) ? $data['product_sku'] : '';
	}

	public function getCpwProductTitle_($data)
	{
		return isset($data['product_name']) ? $data['product_name'] : '';
	}

}