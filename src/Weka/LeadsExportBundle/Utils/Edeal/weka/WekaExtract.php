<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class WekaExtract extends BaseMapping {

	public function getCpwCorpName($data)
	{
		if(isset($data['type-etablissement']))
			return $this->getTypeEtablissement($data['type-etablissement']) . ' - ' . $data['zip'];
		return 'undefined';
	}

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
		return '';
	}

	public function getCpwTypeDemande_($data)
	{
		return 'EXTRAITGRATUIT';
	}

	public function getCpwSku_($data)
	{
		return isset($data['product_sku']) ? $data['product_sku'] : '';
	}

	public function getCpwProductTitle_($data)
	{
		return isset($data['product_name']) ? $data['product_name'] : '';
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