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
     *
     * Get the last N leads for a specific form
     *
     * @param Form $form
     * @param $searchInHistoryOfNbPost
     * @return Paginator
     */
    public function findLastNByType(Form $form, $searchInHistoryOfNbPost)
    {

        $dql = 'SELECT l FROM TellawLeadsFactoryBundle:Leads l JOIN l.form f';
        $dql .= " WHERE f.id=:formId";
        $dql .= " ORDER BY l.createdAt DESC";

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('formId', $form->getId())
            ->setMaxResults($searchInHistoryOfNbPost);


        $result_array = $query->getArrayResult();

        return $result_array;

    }

    /**
     * Get al fields for a Lead
     * @return mixed
     */
    public function getLeadsArrayById($id = 0)
    {

        $q = $this->getEntityManager()
            ->createQueryBuilder();
        $q->select("l")
            ->from("TellawLeadsFactoryBundle:Leads", "l")
            ->where("l.id = " . $id);

        $result_array = $q->getQuery()->getArrayResult();
        $result_object = $q->getQuery()->getResult();

        if ($result_array) {
            $result_array = $result_array[0];
            $result_object = $result_object[0];

            $result_array["content"] = json_decode($result_array["data"], true);

            $result_array["formId"] = $result_object->getForm()->getId();
            $result_array["formName"] = str_replace(' ', '_', strtolower($result_object->getForm()->getName()));

            if ($result_object->getForm()->getFormType()) {
                $result_array["formTypeId"] = $result_object->getForm()->getFormType()->getId();
                $result_array["formTypeName"] = str_replace(' ', '_', strtolower($result_object->getForm()->getFormType()->getName()));
            } else {
                $result_array["formTypeId"] = 0;
                $result_array["formTypeName"] = "";
            }

            if ($result_object->getUser()) {
                $result_array["userId"] = $result_object->getUser()->getId();
                $result_array["userLastName"] = strtolower($result_object->getUser()->getFirstName());
                $result_array["userFirstName"] = strtolower($result_object->getUser()->getLastName());
                $result_array["userEmail"] = strtolower($result_object->getUser()->getEmail());
                $result_array["userName"] = strtolower($result_object->getUser()->getFirstName() . " " . $result_object->getUser()->getLastName());
            } else {
                $result_array["userId"] = 0;
                $result_array["userLastName"] = "";
                $result_array["userFirstName"] = "";
                $result_array["userName"] = "";
                $result_array["userEmail"] = "";
            }

            if ($result_object->getForm()->getScope()) {
                $result_array["scopeId"] = $result_object->getForm()->getScope()->getId();
                $result_array["scopeName"] = str_replace(' ', '_', strtolower($result_object->getForm()->getScope()->getName()));
            } else {
                $result_array["scopeId"] = 0;
                $result_array["scopeName"] = "";
            }

            $result_array["clientId"] = $result_object->getClient();
            $result_array["entrepriseId"] = $result_object->getEntreprise();
        }

        return $result_array;
    }

    /**
     * Returns a paginated list of leads
     *
     * @param int $page
     * @param int $limit
     * @param null $args
     *
     * @return Paginator
     */
    public function getList($page = 1, $limit = 25, $keyword = '', $args = null)
    {
        $dql = $this->getSqlFilterQuery($args);
        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setFirstResult(($page - 1) * $limit)
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

        if (!empty($args)) {
            $dql .= ' WHERE 1=1';

            if (!empty($args['user']) && $args['user']->getScope() != null) {
                $dql .= " AND f.scope = " . $args['user']->getScope()->getId();
            }

            if (isset($args['affectation']) && is_array($args['affectation'])) {
                if (isset($args['affectation']) && !empty($args['affectation'])) {
                    $ids = array();
                    foreach ($args['affectation'] as $affectation) {
                        $ids[] = $affectation->getId();
                    }
                    $dql .= " AND l.user IN ( " . implode(',', $ids) . ") ";
                }
            } else {
                if (isset($args['affectation']) && !empty($args['affectation'])) {
                    $dql .= " AND l.user = " . $args['affectation']->getId();
                }
            }
            if (isset($args['utmcampaign']) && !empty($args['utmcampaign'])) {
                $dql .= " AND l.utmcampaign = '" . $args['utmcampaign'] . "'";
            }

            if (isset($args['workflowStatus']) && !empty($args['workflowStatus'])) {
                $dql .= " AND l.workflowStatus = '" . $args['workflowStatus'] . "'";
            }

            if (isset($args['workflowTheme']) && !empty($args['workflowTheme'])) {
                $dql .= " AND l.workflowTheme = '" . $args['workflowTheme'] . "'";
            }

            if (isset($args['workflowType']) && !empty($args['workflowType'])) {
                $dql .= " AND l.workflowType = '" . $args['workflowType'] . "'";
            }

            if (!empty($args['form'])) {
                $dql .= " AND l.form='{$args['form']}'";
            }

            if (!empty($args['scope'])) {
                $dql .= " AND f.scope='{$args['scope']}'";
            }

            if (!empty($args['lastname'])) {
                $dql .= " AND l.lastname LIKE '%{$args['lastname']}%'";
            }

            if (!empty($args['firstname'])) {
                $dql .= " AND l.firstname LIKE '%{$args['firstname']}%'";
            }

            if (!empty($args['email'])) {
                $dql .= " AND l.email LIKE '%{$args['email']}%'";
            }

            if (!empty($args['keyword'])) {
                $keywords = explode(' ', $args['keyword']);
                foreach ($keywords as $key => $keyword) {
                    //if($key>0)
                    $dql .= ' AND';
                    $dql .= " l.data LIKE '%" . $keyword . "%'";
                }
            }

            if (!empty($args['datemin'])) {
                $datemin = is_object($args['datemin']) ? $args['datemin']->format('Y-m-d') : $args['datemin'];
                if (is_array($datemin)) $datemin = $datemin["date"];
                $dql .= " AND l.createdAt >= '$datemin'";
            }

            if (!empty($args['datemax'])) {
                $datemax = is_object($args['datemax']) ? $args['datemax']->format('Y-m-d') : $args['datemax'];
                if (is_array($datemax)) $datemax = $datemax["date"];
                $dql .= " AND l.createdAt <= '$datemax'";
            }
        }

        $dql .= " ORDER BY l.createdAt DESC";

        return $dql;
    }

    /**
     *
     * Get the 6 months not modify leads
     *
     * @param Users user
     * @param int month
     * @return Paginator
     */
    public function findLastNotModify(Users $user, $month = 6)
    {

        $dql = 'SELECT l FROM TellawLeadsFactoryBundle:Leads l';
        $dql .= " WHERE l.user=:user";
        $dql .= " AND l.modifyAt < :dateModify";

        $rangeMonths = new \DateTime('-'.$month.' months');

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('user', $user)
            ->setParameter('dateModify', $rangeMonths->format('Y-m-d'));


        return $query->getResult();
    }


}

