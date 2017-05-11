<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * MKGSegmentation Repository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MkgSegmentationRepository extends EntityRepository
{

    /**
     * @param $keyword
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getList($page = 1, $limit = 10, $keyword = '', $params = array())
    {

        //Get User scope
        $user = $params["user"];

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('f');
        $qb->from('TellawLeadsFactoryBundle:MkgSegmentation', 'f');

        if ($user->getScope() != null) {
            $qb->where('f.scope = :scope');
            $qb->setParameter('scope', $user->getScope());
        }

        if (!empty($keyword)) {

            $keywords = explode(' ', $keyword);
            foreach ($keywords as $key => $keyword) {
                $qb->where('f.name LIKE :keyword');
                $qb->setParameter('keyword', '%' . $keyword . '%');
            }
        }

        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);

        return new Paginator($qb);

    }

}

