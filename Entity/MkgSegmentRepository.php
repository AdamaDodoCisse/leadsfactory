<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 07/12/15
 * Time: 12:09
 */

namespace Tellaw\LeadsFactoryBundle\Entity;


use Doctrine\ORM\EntityRepository;

class MkgSegmentRepository extends EntityRepository
{

    /**
     * @param $id
     * @return array
     */
    public function getListBySegmentation($id) {
        $dql = 'SELECT s FROM TellawLeadsFactoryBundle:MkgSegment s WHERE s.segmentation_id = '.$id;

        $query = $this->getEntityManager()
            ->createQuery($dql);

        return $query->getScalarResult();
    }

    /**
     * @param $id
     * @return array
     */
    public function getSegmentation($id) {
        $dql = 'SELECT s FROM TellawLeadsFactoryBundle:MkgSegmentation s WHERE s.id = '.$id;

        $query = $this->getEntityManager()
            ->createQuery($dql);

        return $query->getScalarResult();
    }
}