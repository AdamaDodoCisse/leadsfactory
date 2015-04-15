<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Utils\LFUtils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

abstract class AbstractEntityController extends AbstractLeadsController {

	public function getList($repository, $page, $limit, $params=null) {
		$collection = $this->getDoctrine()->getRepository($repository)->getList($page, $limit, $params);

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