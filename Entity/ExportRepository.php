<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;

/**
 * ExportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExportRepository extends EntityRepository
{

    /**
     * @param $keyword
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getList($page=1, $limit=10, $keyword='', $params=array())
    {
        $dql = 'SELECT e FROM TellawLeadsFactoryBundle:Export e';

        if(!empty($keyword)){
            $where = ' WHERE';
            $keywords = explode(' ', $keyword);
            foreach($keywords as $key => $keyword){
                if($key>0)
                    $where .= ' AND';
                $where .= " e.method LIKE '%".$keyword."%'";
            }
            $dql .= $where;
        }

        $dql .= " ORDER BY e.created_at DESC";

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page-1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

    public function findByEmailWaitingValidation($email)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('TellawLeadsFactoryBundle:Export', 'e')
            ->innerJoin('TellawLeadsFactoryBundle:Leads', 'l', 'WITH', 'e.lead = l.id')
            ->where('l.email = :email')
            ->andWhere('e.status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', ExportUtils::EXPORT_EMAIL_NOT_CONFIRMED)
        ;
        return $qb->getQuery()->getResult();
    }
}
