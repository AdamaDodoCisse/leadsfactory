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
class EntrepriseRepository extends EntityRepository
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

        $dql = 'SELECT e FROM TellawLeadsFactoryBundle:Entreprise e';

        $where = "";

        if (!empty($keyword)) {

            $keywords = explode(' ', $keyword);
            foreach ($keywords as $key => $keyword) {
                $where .= " AND e.name LIKE '%" . $keyword . "%'";
            }

        }

        $dql .= $where;

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

}
