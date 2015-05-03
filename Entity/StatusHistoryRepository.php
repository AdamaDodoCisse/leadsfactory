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
class StatusHistoryRepository extends EntityRepository
{


    public function findByStatusDateAndForm ( $statusDate, $form ) {

        $dql = "SELECT hs FROM TellawLeadsFactoryBundle:StatusHistory hs WHERE hs.statusDate = :status_date AND hs.form=:form_id";
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('form_id', $form->getId() )->setParameter('status_date', $statusDate->format('Y-m-d') )->getResult();

        return $result;


    }

}
