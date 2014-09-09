<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
    public function indexAction(Request $request)
    {
        $data = array();

        $formBuilder = $this->createFormBuilder($data);

        $formBuilder
            ->setMethod('POST')
            ->add('period', 'choice',
                array(
                    'choices' => array(
                        'year'  => 'Année',
                        'month' => 'Mois'
                    ),
                    'label' => 'Période'
                    //'attr' => array('onchange'  => 'javascript:alert("xxxx")')
                )
            )
            ->add('form_type', 'choice',
                array(
                    'choices'   => $this->getFormTypesOptions(),
                    'label'     => 'Type',
                    'required'  => false
                )
            )
            ->add('ok', 'submit');

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        return array(
            'form'       => $form->createView()
        );
    }

    /**
     * @route("/chart", name="_monitoring_chart")
     * @Secure(roles="ROLE_USER")
     * @template("TellawLeadsFactoryBundle:monitoring:chart.html.twig")
     */
    public function chartAction($period='month', $formType=null)
    {
        $formType = (empty($formType) || is_array($formType)) ? $formType : array($formType);

        $chart = $this->get('chart');
        $chart->setPeriod($period);
        $chart->setFormType($formType);

        return array(
            'chart_data'    => $chart->loadChartData(),
            'time_range'    => $chart->getTimeRange(),
            'chart_title'   => $chart->getChartTitle()
        );
    }

    private function getFormTypesOptions()
    {
        $formTypes = $this->get('doctrine')->getRepository('TellawLeadsFactoryBundle:FormType')->findAll();
        $array = array('' => 'Tous');
        foreach($formTypes as $formType){
            $array[$formType->getId()] = $formType->getName();
        }

        return $array;
    }

} 