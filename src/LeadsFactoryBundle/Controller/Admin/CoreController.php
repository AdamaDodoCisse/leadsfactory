<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 07:56
 */
namespace LeadsFactoryBundle\Controller\Admin;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CoreController extends Controller
{

    public $logger;

    public function __construct()
    {
    }

    protected function getList($repository, $page, $limit, $keyword, $params = null)
    {

        $collection = $this->getDoctrine()->getRepository($repository)->getList($page, $limit, $keyword, $params);

        return $collection;

    }


}