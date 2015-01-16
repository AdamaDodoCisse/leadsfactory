<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;

class WekaEssai extends AbstractMapping {

	public function getCpwComment($data)
	{
		$comment = 'Provient du formulaire Essai Weka';

		$comment .= "\nNom produit : " . $data['product_name'];
		$comment .= "\nSKU : " . $data['product_sku'];

		return $comment;
	}

	public function getCpwOriIDCode($data)
	{
		return 'ESSAI';
	}

	public function getCpwActIDCode($data)
	{
		return 'tmk';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'Essai';
	}

	public function getCpwSku_($data)
	{
		return $data['product_sku'];
	}

	public function getCpwProductTitle_($data)
	{
		return $data['product_name'];
	}

}