<?php

namespace Weka\LeadsExportBundle\Utils\Edeal;

class DevisPack extends AbstractMapping {

	public function getCpwOriIDCode($data)
	{
		return 'CLASSIC';
	}

	public function getCpwActIDCode($data)
	{
		return '';
	}

	public function getCpwPaysCode($data)
	{
		return $data['pays'];
	}

	public function getCpwComment($data)
	{
		$comment = 'Provient du formulaire de demande de devis pack';

		if(isset($data['referrer_url']))
			$comment .= "\nURL : ".$data['referrer_url'];

		if(!empty($data['product_name']))
			$comment .= "\nNom produit : " . $data['product_name'];

		if(!empty($data['product_sku']))
			$comment .= "\nSKU : " . $data['product_sku'];

		return $comment;
	}

}