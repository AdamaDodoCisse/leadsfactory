<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ExportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FormRepository extends EntityRepository
{
    /** @var  array */
    private $internal_email_patterns = array();

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
        $qb->from('LeadsFactoryBundle:Form', 'f');
        $qb->where ('1=1');

        if ($user->getScope() != null) {
            $qb->andWhere('f.scope = :scope');
            $qb->setParameter('scope', $user->getScope());
        }

        if (!empty($keyword)) {

            $keywords = explode(' ', trim($keyword));
            foreach ($keywords as $key => $keyword) {
                $qb->andWhere('f.name LIKE :keyword');
                $qb->setParameter('keyword', '%' . $keyword . '%');
            }

        }

        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);

        $query = $qb->getQuery();
        return $query->getResult();

    }

    public function getBookmarkedFormsForUser($user_id)
    {

        $dql = "SELECT b FROM LeadsFactoryBundle:Bookmark b WHERE b.user = :user_id AND b.entity_name='Form'";
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('user_id', $user_id)->getResult();

        return $result;

    }

    /**
     * Method used to extract form ID, Page Views, Number of Leads and Transform Rate
     * @param $forms
     * @param $utils
     * @return array|\Doctrine\ORM\Query
     */
    public function getStatisticsForForms($forms, $utils)
    {
        $userPreferences = $utils->getUserPreferences();

        $minDate = date_format($userPreferences->getDataPeriodMinDate(), 'Y-m-d');
        $maxDate = date_format($userPreferences->getDataPeriodMaxDate(), 'Y-m-d');

        // Get type of forms in the user's scope
        $forms_id = array();
        foreach ($forms as $f) $forms_id[] = $f->getId();
        $ids = join(',', $forms_id);

        // Get the number of leads
        $sub_qb_l = $this->_em->createQueryBuilder();
        $sub_qb_l->select('count (l)');
        $sub_qb_l->from("LeadsFactoryBundle:Leads", "l");
        $sub_qb_l->where("l.form = f");
        $sub_qb_l->andWhere("DATE_FORMAT(l.createdAt, '%Y-%m-%d') >= '$minDate'");
        $sub_qb_l->andWhere("DATE_FORMAT(l.createdAt, '%Y-%m-%d') <= '$maxDate'");
        $sub_qb_l = $this->excludeInternalLeads($sub_qb_l);

        // Get the number of views
        $sub_qb_t = $this->_em->createQueryBuilder();
        $sub_qb_t->select('count (t)');
        $sub_qb_t->from("LeadsFactoryBundle:Tracking", "t");
        $sub_qb_t->where("t.form = f");
        $sub_qb_t->andWhere("DATE_FORMAT(t.created_at, '%Y-%m-%d') >= '$minDate'");
        $sub_qb_t->andWhere("DATE_FORMAT(t.created_at, '%Y-%m-%d') <= '$maxDate'");

        // Create query with all subqueries
        $qb = $this->_em->createQueryBuilder();
        $qb->select("f.id, f.name");
        $qb->addSelect("(" . $sub_qb_l->getDQL() . ") AS NB_LEADS");
        $qb->addSelect("(" . $sub_qb_t->getDQL() . ") AS PAGES_VIEWS");
        $qb->from("LeadsFactoryBundle:Form", "f", "f.id");
        if ($ids) $qb->where("f.id IN ($ids)");

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function setStatisticsForId($form_id, $utils)
    {
        // Get forms in this type
        $form = $this->find($form_id);

        /** @var LeadsFactoryBundle\Entity\UserPreferences $userPreferences */
        $userPreferences = $utils->getUserPreferences();

        $minDate = $userPreferences->getDataPeriodMinDate();
        $maxDate = $userPreferences->getDataPeriodMaxDate();

        // Load the number of pages views
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(t)')
            ->from('LeadsFactoryBundle:Tracking', 't')
            ->where('t.form = :formId')
            ->andWhere('t.created_at >= :minDate')
            ->andWhere('t.created_at <= :maxDate')
            ->setParameter('formId', $form_id)
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        $nbviews = $qb->getQuery()->getSingleScalarResult();

        // Load the number of submited forms
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(l)')
            ->from('LeadsFactoryBundle:Leads', 'l')
            ->where('l.form = :formId')
            ->andWhere('l.createdAt >= :minDate')
            ->andWhere('l.createdAt <= :maxDate')
            ->setParameter('formId', $form_id)
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

        $form->nbViews = $nbviews;
        $form->nbLeads = $nbleads;
        $form->transformRate = $transformRate;

        return $form;

    }

    public function getUtmLinkedToForm($form_id)
    {

        $dql = 'SELECT DISTINCT (t.utm_campaign) as utm FROM LeadsFactoryBundle:Tracking t, TellawLeadsFactoryBundle:Form f  WHERE t.form = f.id AND f.id = :formId';
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('formId', $form_id)->getResult();

        return $result;
    }

    /**
     * Method used to extract count for best UTMS
     * @param $forms
     * @param $utils
     * @return array
     */
    public function getStatisticsForUtmForms($forms, $utils)
    {
        $utm = array();
        $userPreferences = $utils->getUserPreferences();

        $minDate = $userPreferences->getDataPeriodMinDate();
        $maxDate = $userPreferences->getDataPeriodMaxDate();


        // La requette doit extraire le top des UTM
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(t.utm_campaign) AS value, t.utm_campaign AS label')
            ->from('LeadsFactoryBundle:Tracking', 't')
            ->where('t.created_at >= :minDate')
            ->andWhere('t.created_at <= :maxDate')
            ->groupBy('t.utm_campaign')
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;

    }

    public function getStatisticsForUtmBookmarks($forms, $bookmarks, $utils)
    {
        $utm = array();
        $userPreferences = $utils->getUserPreferences();

        $minDate = $userPreferences->getDataPeriodMinDate();
        $maxDate = $userPreferences->getDataPeriodMaxDate();

        $bookmarkIds = array();
        foreach ($bookmarks as $bookmark) {
            $bookmarkIds[] = $bookmark->getForm()->getId();
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(t.utm_campaign) AS value, t.utm_campaign AS label')
            ->from('LeadsFactoryBundle:Tracking', 't')
            ->where('t.created_at >= :minDate')
            ->andWhere('t.created_at <= :maxDate')
            ->andWhere('t.form IN (:ids)')
            ->groupBy('t.utm_campaign')
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->setParameter('ids', $bookmarkIds);

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function getStatisticsForUtmInForm($utm, $form_id, $utils)
    {

        $item = array();

        /** @var LeadsFactoryBundle\Entity\UserPreferences $userPreferences */
        $userPreferences = $utils->getUserPreferences();

        $minDate = $userPreferences->getDataPeriodMinDate();
        $maxDate = $userPreferences->getDataPeriodMaxDate();

        // Load the number of pages views
        $dql = 'SELECT  count(f) as nbviews
                        FROM LeadsFactoryBundle:Tracking t, LeadsFactoryBundle:Form f
                        WHERE t.form = f.id
                        AND f.id = :formId
                        AND t.utm_campaign = :utm
                        AND t.created_at >= :minDate
                        AND t.created_at <= :maxDate';

        $result = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('formId', $form_id)
            ->setParameter('utm', $utm)
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->getResult();

        $nbviews = $result[0]["nbviews"];

        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(l)')
            ->from('LeadsFactoryBundle:Leads', 'l')
            ->where('l.form = :formId')
            ->setParameter('formId', $form_id)
            ->andWhere('l.utmcampaign = :utm')
            ->andWhere('l.createdAt >= :minDate')
            ->andWhere('l.createdAt <= :maxDate')
            ->setParameter('utm', $utm)
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

        $item["utm"] = $utm;
        $item["nbViews"] = $nbviews;
        $item["nbLeads"] = $nbleads;
        $item["transformRate"] = $transformRate;

        return $item;

    }

    public function setInternalEmailPatterns($patterns)
    {
        $this->internal_email_patterns = $patterns;
    }

    public function getInternalEmailPatterns()
    {
        return $this->internal_email_patterns;
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
            $qb->andWhere("l.email NOT LIKE '" . $pattern . "'");
            ++$i;
        }

        return $qb;
    }


    public function getForms($scope = null)
    {

        $dql = "SELECT f FROM LeadsFactoryBundle:Form f";
        $result = $this->getEntityManager()->createQuery($dql)->getResult();

        return $result;

    }

    public function getUserForms($user)
    {
        $scope = $user->getScope();
        $forms = $this->findByScope($scope);

        return $forms;
    }

}
