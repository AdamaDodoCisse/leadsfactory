<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;

/**
 * CronTaskRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CronTaskRepository extends EntityRepository
{

    /**
     * @param $keyword
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getList($page=1, $limit=10, $keyword='', $params=array())
    {

        $dql = 'SELECT ct FROM TellawLeadsFactoryBundle:CronTask ct';

        if(!empty($keyword)){
            $where = ' WHERE';
            $keywords = explode(' ', $keyword);
            foreach($keywords as $key => $keyword){
                if($key>0)
                    $where .= ' AND';
                $where .= " ct.name LIKE '%".$keyword."%' OR  ct.commands LIKE '%".$keyword."%'";
            }
            $dql .= $where;
        }

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page-1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

}
