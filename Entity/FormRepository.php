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
    public function getList($page=1, $limit=10, $keyword='', $params=array())
    {

        $dql = 'SELECT f FROM TellawLeadsFactoryBundle:Form f';

        if(!empty($keyword)){
            $where = ' WHERE';
            $keywords = explode(' ', $keyword);
            foreach($keywords as $key => $keyword){
                if($key>0)
                    $where .= ' AND';
                $where .= " f.name LIKE '%".$keyword."%'";
            }
            $dql .= $where;
        }

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page-1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

    public function getBookmarkedFormsForUser ( $user_id ) {

        $dql = "SELECT b FROM TellawLeadsFactoryBundle:Bookmark b WHERE b.user = :user_id AND b.entity_name='Form'";
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('user_id', $user_id )->getResult();

        return $result;

    }

	public function setStatisticsForId($form_id)
	{
		// Get forms in this type
		$form = $this->find( $form_id );

		// Load the number of pages views
		$qb = $this->_em->createQueryBuilder();
		$qb->select('count(t)')
		   ->from('TellawLeadsFactoryBundle:Tracking', 't')
		   ->where('t.form = :formId')
		   ->setParameter('formId', $form_id )
		;
		$nbviews = $qb->getQuery()->getSingleScalarResult();

		// Load the number of submited forms
		$qb = $this->_em->createQueryBuilder();
		$qb->select('count(l)')
		    ->from('TellawLeadsFactoryBundle:Leads', 'l')
		    ->where('l.form = :formId')
		    ->setParameter('formId', $form_id )
		;
		$qb = $this->excludeInternalLeads($qb);
		$nbleads = $qb->getQuery()->getSingleScalarResult();

		// Calculate the transformation rate
		if ($nbviews > 0) {
			$transformRate = round (($nbleads/$nbviews)*100);
		} else {
			$transformRate = 0;
		}

		$form->nbViews = $nbviews;
		$form->nbLeads = $nbleads;
		$form->transformRate = $transformRate;

		return $form;

	}

    public function getUtmLinkedToForm ( $form_id ) {

        $dql = 'SELECT DISTINCT (t.utm_campaign) as utm FROM TellawLeadsFactoryBundle:Tracking t, TellawLeadsFactoryBundle:Form f  WHERE t.form = f.id AND f.id = :formId';
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('formId', $form_id )->getResult();

        return $result;

    }

    public function getStatisticsForUtmInForm ( $utm, $form_id ) {

        $item = array();

        // Load the number of pages views
        $dql = 'SELECT count(f) as nbviews FROM TellawLeadsFactoryBundle:Tracking t, TellawLeadsFactoryBundle:Form f  WHERE t.form = f.id AND f.id = :formId AND t.utm_campaign = :utm';
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('formId', $form_id )->setParameter('utm', $utm )->getResult();
        $nbviews = $result[0]["nbviews"];

	    $qb = $this->_em->createQueryBuilder();
	    $qb->select('count(l)')
	       ->from('TellawLeadsFactoryBundle:Leads', 'l')
	       ->where('l.form = :formId')
	       ->setParameter('formId', $form_id )
	       ->andWhere('l.utmcampaign = :utm')
	       ->setParameter('utm', $utm)
	    ;
	    $qb = $this->excludeInternalLeads($qb);
	    $nbleads = $qb->getQuery()->getSingleScalarResult();

        // Calculate the transformation rate
        if ($nbviews > 0) {
            $transformRate = round (($nbleads/$nbviews)*100);
        } else {
            $transformRate = 0;
        }

        $item["utm"] = $utm;
        $item["nbViews"] = $nbviews;
        $item["nbLeads"] = $nbleads;
        $item["transformRate"] = $transformRate;

        return $item;

    }

    /*public function getStatisticsForUtm ( $utm, $form_id ) {

        $item = array();

        // Load the number of pages views
        $dql = 'SELECT count(f) as nbviews FROM TellawLeadsFactoryBundle:Tracking t, TellawLeadsFactoryBundle:Form f  WHERE t.form = f.id AND t.utm_campaign = :utm';
        $result = $this->getEntityManager()->createQuery($dql)->setParameter('utm', $utm )->getResult();
        $nbviews = $result[0]["nbviews"];

        // Load the number of submited forms
	    $qb = $this->_em->createQueryBuilder();
	    $qb->select('count(l)')
	        ->from('TellawLeadsFactoryBundle:Leads', 'l')
	        ->where('l.form = :formId')
	        ->setParameter('formId', $form_id )
		    ->andWhere('t.utm_campaign = :utm')
		    ->setParameter('utm', $utm)
	    ;
	    $qb = $this->excludeInternalLeads($qb);
	    $nbleads = $qb->getQuery()->getSingleScalarResult();

        // Calculate the transformation rate
        if ($nbviews > 0) {
            $transformRate = round (($nbleads/$nbviews)*100);
        } else {
            $transformRate = 0;
        }

        $item["nbViews"] = $nbviews;
        $item["nbLeads"] = $nbleads;
        $item["transformRate"] = $transformRate;

        return $item;

    }*/

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
			$qb->andWhere('l.email not like :pattern_'.$i)
			   ->setParameter('pattern_'.$i, $pattern)
			;
			++$i;
		}
		return $qb;
	}


    public function getForms ( $scope = null ) {

        $dql = "SELECT f FROM TellawLeadsFactoryBundle:Form f";
        $result = $this->getEntityManager()->createQuery($dql)->getResult();

        return $result;

    }

}
