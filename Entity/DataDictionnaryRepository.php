<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ExportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DataDictionnaryRepository extends EntityRepository
{

	public function findByCodeAndScope ( $code, $scopeId ) {

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('e')
			->from('TellawLeadsFactoryBundle:DataDictionnary', 'e')
			->where('e.code = :code');

		$qb->andWhere('e.scope = :scopeId');

		$qb->setParameter('code', $code);
		$qb->setParameter('scopeId', $scopeId);

		$query = $qb->getQuery();
		try {
			$result = $query->getResult();
		} catch (\Exception $e) {
			$result = '';
		}
		return $result;

	}

    /**
     * @param $keyword
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getList($page=1, $limit=10, $keyword='', $params=array())
    {

        //Get User scope
        $user = $params["user"];

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select ('f');
		$qb->from ('TellawLeadsFactoryBundle:DataDictionnary', 'f');

		if ($user->getScope() != null) {
			$qb->where ('f.scope = :scope');
			$qb->setParameter ('scope',$user->getScope() );
		}

		if(!empty($keyword)){

			$keywords = explode(' ', $keyword);
			foreach($keywords as $key => $keyword){
				$qb->where ('f.name LIKE :keyword');
				$qb->setParameter ('keyword','%'.$keyword.'%' );
			}
		}

		$qb->setFirstResult(($page-1) * $limit);
		$qb->setMaxResults($limit);

		return new Paginator($qb);

    }

	/**
	 * @param string $code
	 * @param array $values
	 *
	 * @return string
	 */
	public function getCommaSeparatedNames($code, $values)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('e.name')
			->from('TellawLeadsFactoryBundle:DataDictionnaryElement', 'e')
			->join('TellawLeadsFactoryBundle:DataDictionnary', 'l')
			->where('l.code = :code')
			->andWhere('e.value IN (:values)')
			->setParameter('code', $code)
			->setParameter('values', $values)
		;
		$names = $qb->getQuery()->getScalarResult();

		$n = count($names);
		if ($n === 0) {
			return '';
		}

		$val = $names[1];
		for ($i=1; $i<$n; ++$i) {
			$val .= ','.$names[$i];
		}
		return $val;
	}

	/**
	 *
	 * Method used to extract list elements by a defined order
	 *
	 * @param $listCode integer id of the list
	 * @param $sortKey must be name or value or rank
	 * @param $sortOrder must be ASC of DESC
	 */
	public function getElementsByOrder ( $listId, $sortKey, $sortOrder, $ignoreStatus = false ) {

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('e')
			->from('TellawLeadsFactoryBundle:DataDictionnaryElement', 'e')
			->where('e.dataDictionnary_id = :listId');

		if (!$ignoreStatus) {
			$qb->andWhere('e.status = 1');
		}

		if (strtolower($sortKey) == "name") {
			$qb->orderBy('e.name', $sortOrder);
		} else if ( strtolower($sortKey) == "value" ) {
			$qb->orderBy('e.value', $sortOrder);
		} else if ( strtolower($sortKey) == "rank" ) {
			$qb->orderBy('e.rank', $sortOrder);
		}

		$qb->setParameter('listId', $listId);

		$query = $qb->getQuery();
		try {
			$result = $query->getResult();
		} catch (\Exception $e) {
			$result = '';
		}
		return $result;

	}

}
