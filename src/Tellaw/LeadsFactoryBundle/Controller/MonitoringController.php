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

        $formBuilder->setMethod('POST')->add('period', 'choice',array(
                    'choices' => array(
                        'year'  => 'Année',
                        'month' => 'Mois'
                    ),
                    'label' => 'Période',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            )
            ->add('form_type', 'choice',
                array(
                    'choices'   => $this->getFormTypesOptions(),
                    'label'     => 'Type',
                    'required'  => false,
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            )
            ->add('form', 'choice',
                array(
                    'choices'   => $this->getFormOptions($request),
                    'label'     => 'Formulaire',
                    'required'  => false,
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            )
            //->add('ok', 'submit')
            ;

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        return array(
            'form'       => $form->createView()
        );
    }

    /**
     * @route("/chart", name="_monitoring_chart")
     * @Secure(roles="ROLE_USER")
     *
     * @var string period
     * @var mixed formType
     * @var mixed form
     */
    public function chartAction($period='year', $formType=null, $form=null)
    {
        $chart = $this->get('chart');
        $chart->setPeriod($period);
        $chart->setFormType($formType);
        $chart->setForm($form);

        $chartData = $chart->loadChartData();

        //Si un type de formulaire est sélectionné on utilise le template chart2.html.twig
        $template = "TellawLeadsFactoryBundle:monitoring:chart_bar.html.twig";

        $data = array(
            'chart_data'        => $chartData,
            'time_range'        => $chart->getTimeRange(),
            'chart_title'       => $chart->getChartTitle(),
            'special_graphs'    => $chart->getSpecialGraphIndexes($chartData) //indexes des graphes à afficher en mode 'ligne' (pour le combo chart)
        );

        return $this->render($template, $data);

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

    private function getFormOptions($request)
    {
        $form = $request->request->get('form');
        $form_type = $form['form_type'];

        if(empty($form_type))
            return array('' => 'Choisissez d\'abord un type');

        $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findByFormType($form_type);

        $options = array('' => 'Sélectionnez');
        foreach($forms as $form){
            $options[$form->getId()] = $form->getName();
        }
        return $options;
    }

} 