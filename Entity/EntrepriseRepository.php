<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * EntrepriseRepository
 *
 * This class manage the relation of the Entity and the request that should be done to retrieve datas.
 *
 * @Author Eric Wallet
 *
 */
class EntrepriseRepository extends EntityRepository
{

    /**
     *
     * This method return a list of Entreprise using Doctrine Paginator
     * It is mostly used for backoffice to display tables of elements
     *
     * @param   String  $keyword Filter words separated by a space to use as a search keyword
     * @param   int     $page Page to display for pagination
     * @param   int     $limit Number of elements in the page
     * @param   array   $params Array of elements the method could receive. Users 'user' key is usually used to filter scope for user.
     *
     * @return Paginator
     */
    public function getList(    $page=1,
                                $limit=10,
                                $keyword='',
                                $params=array())
    {

        //Get User scope
        $user = $params["user"];

        $dql = 'SELECT e FROM TellawLeadsFactoryBundle:Entreprise e';

        $where = "";

        if(!empty($keyword)){

            $keywords = explode(' ', $keyword);
            foreach($keywords as $key => $keyword){
                $where .= " AND e.name LIKE '%".$keyword."%'";
            }

        }

        $dql .= $where;

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page-1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

}
