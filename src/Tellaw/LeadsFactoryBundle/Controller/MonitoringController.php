<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tellaw\LeadsFactoryBundle\Utils\Chart;

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

    /**
     * @route("/chart", name="_monitoring_chart")
     * @Secure(roles="ROLE_USER")
     * @template("TellawLeadsFactoryBundle:monitoring:chart.html.twig")
     */
    public function chartAction($period='month', $formType=null)
    {
        $chart = $this->get('chart');
        $chart->setPeriod($period);
        $chart->setFormType($formType);

        return array(
            'chart_data'    => $chart->loadChartData(),
            'time_range'    => $chart->getTimeRange(),
            'chart_title'   => $chart->getChartTitle()
        );
    }

} 