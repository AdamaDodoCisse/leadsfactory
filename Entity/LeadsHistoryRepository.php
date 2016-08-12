<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * LeadsCommentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LeadsHistoryRepository extends EntityRepository
{

    public function getHistoryForLead($leadId)
    {

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('e')
            ->from('TellawLeadsFactoryBundle:LeadsHistory', 'e')
            ->where('e.lead_id = :leadId');

        $qb->orderBy('e.created_at', 'DESC');

        $qb->setParameter('leadId', $leadId);

        $query = $qb->getQuery();
        try {
            $result = $query->getResult();
        } catch (\Exception $e) {
            throw new \Exception ($e);
        }

        return $result;

    }

}

