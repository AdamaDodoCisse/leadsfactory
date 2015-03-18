<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\weka;

use Weka\LeadsExportBundle\Utils\Edeal\BaseMapping;

class WekaWebcallback extends BaseMapping {

	public function getCpwCorpName($data)
	{
		if(isset($data['type-etablissement']))
			return $this->getTypeEtablissement($data['type-etablissement']) . ' - ' . $data['zip'];
		return 'undefined';
	}

	public function getEntCorpName($data)
	{
		return $this->getCpwCorpName($data);
	}

	public function getCpwOriIDCode($data)
	{
		return $this->getOrigine($data);
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwTypeDemande_($data)
	{
		return $this->getOrigine($data);
	}

	public function getCpwSku_($data)
	{
		return isset($data['product_sku']) ? $data['product_sku'] : '';
	}

	public function getCpwProductTitle_($data)
	{
		return isset($data['product_name']) ? $data['product_name'] : '';
	}

	public function getCpwComment($data)
	{
		$comment = 'Provient du formulaire de Web call back';

		if(isset($data['referrer_url']))
			$comment .= "\nURL : ".$data['referrer_url'];

		if(!empty($data['product_name']))
			$comment .= "\nNom produit : " . $data['product_name'];

		if(!empty($data['product_sku']))
			$comment .= "\nSKU : " . $data['product_sku'];

		return $comment;
	}

	public function getCpwPaysCode($data)
	{
		return 'FR';
	}

	protected function getOrigine($data)
	{
		if(!empty($data['wcb_type'])){
			if($data['wcb_type'] == 'logiciels')
				$origine = $data['wcb_type'] == 'logiciels' ? 'WEBCALLBACK_LOGICIEL' : 'WEBCALLBACK_RESDOC';
		}else{
			$origine = 'WEBCALLBACK_RESDOC';
		}

		return $origine;
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