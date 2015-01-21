<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ReferenceListElementRepository
 *
 */
class ReferenceListElementRepository extends EntityRepository
{

	/**
	 * Retourne le libellé correspondant à la valeur d'une option
	 *
	 * @param $listId
	 * @param $value
	 *
	 * @return string
	 */
	public function getLabel($listId, $value)
	{
		$listElement = $this->findOneBy(array ('referencelist_id' => $listId, 'value' => $value));

		return $listElement->getName();
	}

}
