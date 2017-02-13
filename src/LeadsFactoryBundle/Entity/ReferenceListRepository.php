<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ExportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReferenceListRepository extends EntityRepository
{

    /**
     * @param int $page
     * @param int $limit
     * @param string $keyword
     * @param array $params
     * @return Paginator
     */
    public function getList($page = 1, $limit = 10, $keyword = '', $params = array())
    {

        //Get User scope
        $user = $params["user"];

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('f');
        $qb->from('TellawLeadsFactoryBundle:ReferenceList', 'f');

        if ($user->getScope() != null) {
            $qb->where('f.scope = :scope');
            $qb->setParameter('scope', $user->getScope());
        }

        if (!empty($keyword)) {

            $keywords = explode(' ', $keyword);
            foreach ($keywords as $key => $keyword) {
                $qb->where('f.name LIKE :keyword');
                $qb->orderBy('f.rank');
                $qb->setParameter('keyword', '%' . $keyword . '%');
            }
        }

        $qb->setFirstResult(($page - 1) * $limit);
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
            ->from('TellawLeadsFactoryBundle:ReferenceListElement', 'e')
            ->join('TellawLeadsFactoryBundle:ReferenceList', 'l')
            ->where('l.code = :code')
            ->andWhere('e.value IN (:values)')
            ->orderBy('e.rank')
            ->setParameter('code', $code)
            ->setParameter('values', $values);
        $names = $qb->getQuery()->getScalarResult();

        $n = count($names);
        if ($n === 0) {
            return '';
        }

        $val = $names[1];
        for ($i = 1; $i < $n; ++$i) {
            $val .= ',' . $names[$i];
        }

        return $val;
    }

    /**
     *
     * Method used to extract list elements by a defined order
     *
     * @param $listId
     * @param $sortKey  //must be name or value or rank
     * @param $sortOrder //must be ASC of DESC
     * @param bool $ignoreStatus
     * @return array|string
     */
    public function getElementsByOrder($listId, $sortKey, $sortOrder, $ignoreStatus = false)
    {

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('TellawLeadsFactoryBundle:ReferenceListElement', 'e')
            ->where('e.referencelist_id = :listId');

        if (!$ignoreStatus) {
            $qb->andWhere('e.status = 1');
        }

        if (strtolower($sortKey) == "name") {
            $qb->orderBy('e.name', $sortOrder);
        } else if (strtolower($sortKey) == "value") {
            $qb->orderBy('e.value', $sortOrder);
        } else if (strtolower($sortKey) == "rank") {
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