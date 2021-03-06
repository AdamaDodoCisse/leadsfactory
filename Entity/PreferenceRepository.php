<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * TrackingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PreferenceRepository extends EntityRepository
{

    /**
     * @param $keyword
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getList($page = 1, $limit = 10, $keyword = '', $params = array())
    {

        $dql = 'SELECT p FROM TellawLeadsFactoryBundle:Preference p';

        if (!empty($keyword)) {
            $where = ' WHERE';
            $keywords = explode(' ', $keyword);
            foreach ($keywords as $key => $keyword) {
                if ($key > 0)
                    $where .= ' AND';
                $where .= " p.keyval LIKE '%" . $keyword . "%' OR p.value LIKE '%" . $keyword . "%'";
            }
            $dql .= $where;
        }

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

    public function findByKey($key)
    {

        $dql = 'SELECT p FROM TellawLeadsFactoryBundle:Preference p WHERE p.keyval = :key';

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameters(array("key" => $key));

        return $query->execute();
    }

    public function findByKeyAndScope($key, $scopeId)
    {

        $dql = 'SELECT p FROM TellawLeadsFactoryBundle:Preference p WHERE p.keyval = :key AND p.scope = :scopeId';

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameters(array("key" => $key, "scopeId" => $scopeId));

        $result = $query->getSingleResult();

        return $result;
    }

}
