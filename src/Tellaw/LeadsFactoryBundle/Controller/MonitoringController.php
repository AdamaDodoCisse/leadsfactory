<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/monitoring")
 */
class MonitoringController extends Controller{

    /**
     * @route("/dashboard", name="_monitoring_dashboard")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function dashboardAction()
    {

    }

    /**
     * @route("/index", name="_monitoring_index")
     * @Secure(roles="ROLE_USER")
     * @template("TellawLeadsFactoryBundle:monitoring:index.html.twig")
     */
    public function indexAction()
    {
        return array('xxx'=>'cc');
    }

} 