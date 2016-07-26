<?php

namespace Weka\LeadsExportBundle\Utils\Gotowebinar\ti;

use Weka\LeadsExportBundle\Utils\Gotowebinar\BaseMapping;


class TIWebinar extends BaseMapping{

	public function getCountry($data)
	{
		$country = $this->list_element_repository->getNameUsingListCode('pays', $data['pays']);
		return !empty($country) ? $country : 'inconnu' ;
	}

	public function getCity($data)
	{
		if(!empty($data['ville_id'])){
			$city = $this->list_element_repository->getNameUsingListCode('ville', $data['ville_id']);
		}elseif(!empty($data['ville_text'])){
			$city = $data['ville_text'];
		}

		return !empty($city) ? $city : 'inconnu' ;
	}

	public function getJobTitle($data)
	{
		$label = $this->list_element_repository->getNameUsingListCode('ti_fonction', $data['fonction']);
		return !empty($label) ? $label : 'inconnu' ;
	}
}
