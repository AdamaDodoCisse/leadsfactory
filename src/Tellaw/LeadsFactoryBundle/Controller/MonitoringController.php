<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tellaw\LeadsFactoryBundle\Entity\Bookmark;
use Tellaw\LeadsFactoryBundle\Utils\Chart;

/**
 * @Route("/monitoring")
 */
class MonitoringController extends AbstractLeadsController{

    /**
     * @route("/dashboard", name="_monitoring_dashboard")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function dashboardAction(Request $request)
    {
        $data = array();

        $formBuilder = $this->createFormBuilder($data);
        $formBuilder->setMethod('POST')
            ->add('period', 'choice', array(
                    'choices' => array(
                        Chart::PERIOD_YEAR  => 'Année',
                        Chart::PERIOD_MONTH => 'Mois'
                    ),
                    'label' => 'Période',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            )
            ->add('mode', 'choice', array(
                    'choices'   => array(
                        'FormType' => 'Types de formulaire',
                        'Form' => 'Formulaires'
                    ),
                    'data'      => 'FormType',
                    'label'     => 'Données',
                    /*'required'  => false,*/
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            );

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $this->render ($this->getBaseTheme().":monitoring:dashboard.html.twig", array(
            'form'  => $form->createView()
        ));
    }

    public function chartDashboardAction($period=Chart::PERIOD_YEAR, $mode='FormType')
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $chart = $this->get('chart');
        $chart->setPeriod($period);

        if($mode == 'FormType'){
            $query = $em->createQuery('SELECT f FROM TellawLeadsFactoryBundle:FormType f, TellawLeadsFactoryBundle:Bookmark b WHERE b.formType = f.id AND b.user ='.$user->getId());
            $formTypes = $query->getResult();
            $chart->setFormType($formTypes);
        }else{
            $query = $em->createQuery('SELECT f FROM TellawLeadsFactoryBundle:Form f, TellawLeadsFactoryBundle:Bookmark b WHERE b.form = f.id AND b.user ='.$user->getId());
            $forms = $query->getResult();
            $chart->setForm($forms);
        }

        $chartData = $chart->loadChartData();

        //Si un type de formulaire est sélectionné on utilise le template chart2.html.twig
        $template = $this->getBaseTheme().":monitoring:chart_bar.html.twig";

        $data = array(
            'chart_data'        => $chartData,
            'time_range'        => $chart->getTimeRange(),
            'chart_title'       => $chart->getChartTitle(),
            'special_graphs'    => $chart->getSpecialGraphIndexes($chartData) //indexes des graphes à afficher en mode 'ligne' (pour le combo chart)
        );

        return $this->render($template, $data);
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function measureDashboardAction($mode = 'FormType')
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT f FROM TellawLeadsFactoryBundle:'.$mode.' f, TellawLeadsFactoryBundle:Bookmark b WHERE b.'.lcfirst($mode).' = f.id AND b.user ='.$user->getId());
        $entities = $query->getResult();

        if($mode == 'FormType'){
            $title = 'Mes types favoris';
        }else{
            $title = 'Mes formulaires favoris';
        }


        $this->render($this->getBaseTheme().":monitoring:measure.html.twig", array(
            'entities'  => $entities,
	        'alerteutil' => $this->get("alertes_utils"),
            'title'  => $title
        ));
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function measureAction($formType=null, $form=null)
    {
        $em = $this->getDoctrine()->getManager();

        if(!empty($form)){
            $form = $em->getRepository('TellawLeadsFactoryBundle:Form')->find($form);
            $entities = array($form);
            $title = 'Formulaire '.$form->getName();
        }elseif(!empty($formType)){
            $entities = $em->getRepository('TellawLeadsFactoryBundle:Form')->findByFormType($formType);
            $title = "Tous les formulaires du type sélectionné";
        }else{
            $entities = $em->getRepository('TellawLeadsFactoryBundle:FormType')->findAll();
            $title = "Tous les types de formulaires";
        }

        $this->render($this->getBaseTheme().":monitoring:measure.html.twig", array(
            'entities'  => $entities,
            'alerteutil' => $this->get("alertes_utils"),
            'title'  => $title
        ));
    }

    /**
     * @route("/index", name="_monitoring_index")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function indexAction(Request $request)
    {
        $data = array();

        $formBuilder = $this->createFormBuilder($data);

        $formBuilder->setMethod('POST')
            ->add('period', 'choice',array(
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
            ;

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $this->render($this->getBaseTheme().":monitoring:index.html.twig", array(
            'form'       => $form->createView()
        ));
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
        if(!empty($form))
            $form = array($form);

        $chart = $this->get('chart');
        $chart->setPeriod($period);
        $chart->setFormType($formType);
        $chart->setForm($form);

        $chartData = $chart->loadChartData();

        //Si un type de formulaire est sélectionné on utilise le template chart2.html.twig
        $template = $this->getBaseTheme().":monitoring:chart_bar.html.twig";

        $data = array(
            'chart_data'        => $chartData,
            'time_range'        => $chart->getTimeRange(),
            'chart_title'       => $chart->getChartTitle(),
            'special_graphs'    => $chart->getSpecialGraphIndexes($chartData) //indexes des graphes à afficher en mode 'ligne' (pour le combo chart)
        );

        return $this->render($template, $data);

    }

    /**
     * @return array
     */
    private function getFormTypesOptions()
    {
        $formTypes = $this->get('doctrine')->getRepository('TellawLeadsFactoryBundle:FormType')->findAll();
        $array = array('' => 'Tous');
        foreach($formTypes as $formType){
            $array[$formType->getId()] = $formType->getName();
        }

        return $array;
    }

    /**
     * @param $request
     * @return array
     */
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

    /**
     * @route("/bookmark", name="_monitoring_bookmark")
     * @Secure(roles="ROLE_USER")
     *
     * @param string $entity Form|FormType
     * @param int $id
     * @param bool $status
     */
    public function bookmarkAction(Request $request)
    {
        $bookmarked = $request->request->get('status');

        if($bookmarked == 'true'){
            $this->createBookmark($request);
        }else{
            $this->deleteBookmark($request);
        }

        return new Response('Done !');
    }

    /**
     * @param $request
     */
    private function createBookmark($request)
    {
        $user = $this->getUser();
        $entity_type = $request->request->get('entity');
        $id = (int) $request->request->get('id');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TellawLeadsFactoryBundle:'.$entity_type)->find($id);

        $entitySetter = 'set'.$entity_type;

        $bookmark = new Bookmark();
        $bookmark->setUser($user);
        $bookmark->setEntityName($entity_type);
        $bookmark->setEntityId($id);
        $bookmark->$entitySetter($entity);


        $em->persist($bookmark);
        $em->flush();
    }

    private function deleteBookmark($request)
    {
        $user = $this->getUser();
        $entity = $request->request->get('entity');
        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();

        $bookmark = $em->getRepository('TellawLeadsFactoryBundle:Bookmark')->findOneBy(array(
           'user'       => $user,
           'entity_name'   => $entity,
           'entity_id'     => $id
        ));

        $em->remove($bookmark);
        $em->flush();
    }

} 