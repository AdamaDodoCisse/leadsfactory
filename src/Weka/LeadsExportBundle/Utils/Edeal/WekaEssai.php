<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;

class WekaEssai extends BaseMapping
{
	public function getCpwCorpName($data)
	{
		if(isset($data['type-etablissement']))
			return $this->getTypeEtablissement($data['type-etablissement']) . ' - ' . $data['zip'];
		return 'undefined';
	}

	public function getCpwComment($data)
	{
		$comment = 'Provient du formulaire Essai Weka';

		if(!empty($data['product_name']))
			$comment .= "\nNom produit : " . $data['product_name'];

		if(!empty($data['product_sku']))
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

	public function getCpwPaysCode($data)
	{
		return 'FR';
	}

	public function getEntCorpName($data)
	{
		if(isset($data['type-etablissement']))
			return $this->getTypeEtablissement($data['type-etablissement']) . ' - ' . $data['zip'];
		return 'undefined';
	}

	public function getEntCtrCode($data)
	{
		return 'FR';
	}

	public function getPerCtrCode($data)
	{
		return 'FR';
	}

}
