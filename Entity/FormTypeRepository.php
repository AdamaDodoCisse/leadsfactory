<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ExportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FormTypeRepository extends EntityRepository
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
        $qb->from('TellawLeadsFactoryBundle:FormType', 'f');

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

    public function getFormsType($scope = null)
    {

        $dql = "SELECT t FROM TellawLeadsFactoryBundle:FormType t";
        $result = $this->getEntityManager()->createQuery($dql)->getResult();

        return $result;

    }

    public function getBookmarkedFormsForUser($user_id)
    {

        $dql = "SELECT b FROM TellawLeadsFactoryBundle:Bookmark b WHERE b.user = :user_id AND b.entity_name='FormType'";
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('user_id', $user_id)->getResult();

        return $result;

    }

    public function getFormsInFormType($formType_id)
    {

        $dql = "SELECT f
                FROM TellawLeadsFactoryBundle:Form f, TellawLeadsFactoryBundle:FormType ft
                WHERE f.formType = ft.id
                AND ft.id=:formType_id";

        $result = $this->getEntityManager()->createQuery($dql)->setParameter('formType_id', $formType_id)->getResult();

        return $result;

    }

    public function setStatisticsForId($formType_id, $utils)
    {

        // Get forms in this type
        $formType = $this->find($formType_id);

        /** @var Tellaw\LeadsFactoryBundle\Entity\UserPreferences $userPreferences */
        $userPreferences = $utils->getUserPreferences();

        $minDate = $userPreferences->getDataPeriodMinDate();
        $maxDate = $userPreferences->getDataPeriodMaxDate();

        // Load the number of pages views
        $dql = 'SELECT count(f) as nbviews
                FROM TellawLeadsFactoryBundle:Tracking t,TellawLeadsFactoryBundle:FormType ft, TellawLeadsFactoryBundle:Form f
                WHERE ft.id = f.formType
                AND t.form = f.id
                AND ft.id = :formTypeId
                AND t.created_at >= :minDate
                AND t.created_at <= :maxDate';


        $result = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('formTypeId', $formType_id)
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->getResult();

        $nbviews = $result[0]["nbviews"];

        // Load the number of leads
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(l)')
            ->from('TellawLeadsFactoryBundle:Leads', 'l')
            ->where('l.formType = :formTypeId')
            ->andWhere('l.createdAt >= :minDate')
            ->andWhere('l.createdAt <= :maxDate')
            ->setParameter('formTypeId', $formType_id)
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        $qb = $this->excludeInternalLeads($qb);
        $nbleads = $qb->getQuery()->getSingleScalarResult();

        // Calculate the transformation rate
        if ($nbviews > 0) {
            $transformRate = round(($nbleads / $nbviews) * 100);
        } else {
            $transformRate = 0;
        }

        $formType->nbViews = $nbviews;
        $formType->nbLeads = $nbleads;
        $formType->transformRate = $transformRate;

        return $formType;

    }

    public function setInternalEmailPatterns($patterns)
    {
        $this->internal_email_patterns = $patterns;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     *
     */
    private function excludeInternalLeads(QueryBuilder $qb)
    {
        $i = 0;
        foreach ($this->internal_email_patterns as $pattern) {
            $qb->andWhere('l.email not like :pattern_' . $i)
                ->setParameter('pattern_' . $i, $pattern);
            ++$i;
        }

        return $qb;
    }
}
