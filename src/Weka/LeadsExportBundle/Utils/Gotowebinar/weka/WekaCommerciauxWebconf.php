<?php

namespace Weka\LeadsExportBundle\Utils\Gotowebinar\weka;

use Weka\LeadsExportBundle\Utils\Gotowebinar\BaseMapping;


class WekaCommerciauxWebconf extends BaseMapping{

	public function getCity($data)
	{
		$city = $this->list_element_repository->getNameUsingListCode('ville', $data['ville_id']);
		return !empty($city) ? $city : 'inconnu' ;
	}

	public function getOrganization($data)
	{
		$label = $this->list_element_repository->getNameUsingListCode('type_etablissement', $data['type-etablissement']);
		return !empty($label) ? $label : 'inconnu' ;
	}

	public function getJobTitle($data)
	{
		$label = $this->list_element_repository->getNameUsingListCode('fonction', $data['fonction']);
		return !empty($label) ? $label : 'inconnu' ;
	}
}
