<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * LeadsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LeadsRepository extends EntityRepository
{
	/**
	 * Returns a paginated list of leads
	 *
	 * @param int $page
	 * @param int $limit
	 * @param null $args
	 *
	 * @return Paginator
	 */
	public function getList($page=1, $limit=25, $args=null)
	{
		$dql = $this->getSqlFilterQuery($args);

		$query = $this->getEntityManager()
		              ->createQuery($dql)
		              ->setFirstResult(($page-1) * $limit)
		              ->setMaxResults($limit);

		return new Paginator($query);
	}

	/**
	 * Returns an iterable query result of leads with no pagination
	 *
	 * @param array $args
	 *
	 * @return \Doctrine\ORM\Internal\Hydration\IterableResult
	 */
	public function getIterableList($args)
	{
		$dql = $this->getSqlFilterQuery($args);
		$results = $this->getEntityManager()
		                ->createQuery($dql)
						->iterate();

		return $results;
	}

	/**
	 * Return an array of leads based on parameters
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function getLeads($args)
	{
		$dql = $this->getSqlFilterQuery($args);
		$results = $this->getEntityManager()->createQuery($dql)->getResult();

		return $results;
	}

	/**
	 * Builds the dql query to filter the leads
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	protected function getSqlFilterQuery($args)
	{
		$dql = 'SELECT l FROM TellawLeadsFactoryBundle:Leads l JOIN l.form f';

		if(!empty($args)) {
			$dql .= ' WHERE 1=1';

			if(!empty($args['form'])){
				$dql .= " AND l.form='{$args['form']}'";
			}

			if(!empty($args['scope'])){
				$dql .= " AND f.scope='{$args['scope']}'";
			}

			if(!empty($args['lastname'])){
				$dql .= " AND l.lastname LIKE '%{$args['lastname']}%'";
			}

			if(!empty($args['firstname'])){
				$dql .= " AND l.firstname LIKE '%{$args['firstname']}%'";
			}

			if(!empty($args['email'])){
				$dql .= " AND l.email LIKE '%{$args['email']}%'";
			}

			if(!empty($args['keyword'])){
				$keywords = explode(' ', $args['keyword']);
				foreach($keywords as $key => $keyword){
					//if($key>0)
					$dql .= ' AND';
					$dql .= " l.data LIKE '%".$keyword."%'";
				}
			}

			if(!empty($args['datemin'])){
				$datemin = is_array($args['datemin']) ? $args['datemin']['date'] : $args['datemin']->format('Y-m-d');
				$dql .= " AND l.createdAt >= '$datemin'";
			}

			if(!empty($args['datemax'])){
				$datemax = is_array($args['datemax']) ? $args['datemax']['date'] : $args['datemax']->format('Y-m-d');;
				$dql .= " AND l.createdAt <= '$datemax'";
			}
		}

		$dql .= " ORDER BY l.createdAt DESC";

		return $dql;
	}
}
