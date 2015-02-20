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
	 * @param string $listCode
	 * @param string $value
	 *
	 * @return string
	 */
	public function getLabel($listCode, $value)
	{
		$sql = "SELECT e.name FROM ReferenceListElement e LEFT JOIN ReferenceList l ON e.referencelist_id=l.id WHERE l.code=:code AND e.value=:value";
		$query = $this->_em->getConnection()->prepare($sql);
		$query->bindValue('code', $listCode);
		$query->bindValue('value', $value);
		$query->execute();
		$label = $query->fetchColumn();

		return $label;
	}

}
