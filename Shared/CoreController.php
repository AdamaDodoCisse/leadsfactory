<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 07:56
 */
namespace Tellaw\LeadsFactoryBundle\Shared;

use Symfony\Bridge\Monolog\Logger;
use Tellaw\LeadsFactoryBundle\Controller\Admin\AbstractLeadsController;
use Tellaw\LeadsFactoryBundle\Shared\CoreManager;

class CoreController extends AbstractLeadsController {

    public $logger;

    public function __construct () {

        //$logger = $this->container->get('export.logger');
        $informations = CoreManager::getLicenceInfos();
        //var_dump($informations);

        //$logger->info ($informations);

    }

    public function getList($repository, $page, $limit, $keyword, $params=null) {

        $collection = $this->getDoctrine()->getRepository($repository)->getList($page, $limit, $keyword, $params);

        $total = $collection->count();
        $pages_count = ceil($total/$limit);

        $pagination = array(
            'page'              => $page,
            'total'             => $total,
            'pages_count'       => $pages_count,
            'pagination_min'    => ($page>5) ? $page -5 : 1,
            'pagination_max'    => ($pages_count - $page) > 5 ? $page +5 : $pages_count,
            'route'             => $this->container->get('request')->get('_route'),
            'limit'             => $limit,
            'keyword'           => ''
        );

        $limitOptions = explode(';', $this->container->getParameter('list.per_page_options'));

        $list = array(
            'collection'    => $collection,
            'pagination'    => $pagination,
            'limit_options' => $limitOptions
        );

        return $list;

    }


}