<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ExportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FieldRepository extends EntityRepository
{

    /**
     * @param $keyword
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getList($page = 1, $limit = 10, $keyword = '', $params = array())
    {

        $dql = 'SELECT f FROM TellawLeadsFactoryBundle:Field f';

        if (!empty($keyword)) {
            $where = ' WHERE';
            $keywords = explode(' ', $keyword);
            foreach ($keywords as $key => $keyword) {
                if ($key > 0)
                    $where .= ' AND';
                $where .= " f.code LIKE '%" . $keyword . "%' OR  f.description LIKE '%" . $keyword . "%'";
            }
            $dql .= $where;
        }

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

    public function findOneByCodeAsArray($code)
    {
        $dql = "SELECT f FROM TellawLeadsFactoryBundle:Field f WHERE f.code ='$code'";
        $result = $this->getEntityManager()
            ->createQuery($dql)
            ->setMaxResults(1)
            ->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return $result;
    }


    public function getListByScopeName($scope_name)
    {
        $dql = 'SELECT f FROM TellawLeadsFactoryBundle:Field f';
        $result = $this->getEntityManager()->createQuery($dql)->getResult();

        return ($result);
    }

}
