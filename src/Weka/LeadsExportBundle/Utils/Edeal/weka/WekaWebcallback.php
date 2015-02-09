<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;

class WekaWebcallback extends AbstractMapping {

	public function getCpwOriIDCode($data)
	{
		return $this->getOrigine($data);
	}

	public function getCpwActIDCode($data)
	{
		return 'tmk';
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
		if(!empty($data['wcp_type'])){
			if($data['wcp_type'] == 'logiciels')
				$origine = $data['wcp_type'] == 'logiciels' ? 'WEBCALLBACK_LOGICIEL' : 'WEBCALLBACK_RESDOC';
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