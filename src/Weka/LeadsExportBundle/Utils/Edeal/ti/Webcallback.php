<?php

namespace Weka\LeadsExportBundle\Utils\Edeal\ti;

class Webcallback extends BaseMapping
{
	public function getCpwOriIDCode($data)
	{
		return 'WEBCALLBACK';
	}

	public function getCpwActIDCode($data)
	{
		return 'TMK';
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

}
