<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

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
                    'label' => 'Période du graphique',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            );
            /*
            ->add('mode', 'choice', array(
                    'choices'   => array(
                        'FormType' => 'Types de formulaire',
                        'Form' => 'Formulaires'
                    ),
                    'data'      => 'FormType',
                    'label'     => 'Données',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            );*/

        // Create the form used for grap configuration
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        // Logged User
        $user_id = $this->get('security.context')->getToken()->getUser()->getId();

        // Load bookmarked types for user
        $bookmarks = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Bookmark")->getTypesForUser( $user_id );

        return $this->render ("TellawLeadsFactoryBundle:monitoring:dashboard.html.twig", array(
            'form'  => $form->createView(),
            'bookmarks' => $bookmarks
        ));
    }

    /**
     * @route("/dashboard_forms", name="_monitoring_dashboard_forms")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardFormsAction(Request $request)
    {
        $data = array();

        $formBuilder = $this->createFormBuilder($data);
        $formBuilder->setMethod('POST')
            ->add('period', 'choice', array(
                    'choices' => array(
                        Chart::PERIOD_YEAR  => 'Année',
                        Chart::PERIOD_MONTH => 'Mois'
                    ),
                    'label' => 'Période du graphique',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            );

        // Create the form used for grap configuration
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        // Logged User
        $user_id = $this->get('security.context')->getToken()->getUser()->getId();

        // Load bookmarked forms for user
        $bookmarks = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Bookmark")->getFormsForUser( $user_id );

        return $this->render ("TellawLeadsFactoryBundle:monitoring:dashboard_forms.html.twig", array(
            'form'  => $form->createView(),
            'bookmarks' => $bookmarks
        ));
    }

    /**
     * @route("/dashboard_type_page/{type_id}", name="_monitoring_dashboard_type_page")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardTypePageAction( Request $request, $type_id )
    {

        $entity = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:FormType")->find( $type_id );

        $data = array();

        $formBuilder = $this->createFormBuilder($data);
        $formBuilder->setMethod('POST')
            ->add('period', 'choice', array(
                    'choices' => array(
                        Chart::PERIOD_YEAR  => 'Année',
                        Chart::PERIOD_MONTH => 'Mois'
                    ),
                    'label' => 'Période du graphique',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            );

        // Create the form used for grap configuration
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        return $this->render ("TellawLeadsFactoryBundle:monitoring:dashboard_type_page.html.twig", array(
            'form'  => $form->createView(),
            'entity' => $entity
        ));

    }

    /**
     * @route("/dashboard_forms_page/{form_id}", name="_monitoring_dashboard_form_page")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardFormPageAction( Request $request, $form_id )
    {
        $entity = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Form")->find( $form_id );

        $data = array();

        $formBuilder = $this->createFormBuilder($data);
        $formBuilder->setMethod('POST')
            ->add('period', 'choice', array(
                    'choices' => array(
                        Chart::PERIOD_YEAR  => 'Année',
                        Chart::PERIOD_MONTH => 'Mois'
                    ),
                    'label' => 'Période du graphique',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            );

        // Create the form used for grap configuration
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        return $this->render("TellawLeadsFactoryBundle:monitoring:dashboard_form_page.html.twig", array(
            'form'  => $form->createView(),
            'entity'   => $entity,
            'alerteutil' => $this->get("alertes_utils"),
        ));
    }

    /**
     * @route("/dashboard_utm_page/{utm}", name="_monitoring_dashboard_utm_page")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardUtmPageAction( Request $request, $utm )
    {

        $data = array();

        $formBuilder = $this->createFormBuilder($data);
        $formBuilder->setMethod('POST')
            ->add('period', 'choice', array(
                    'choices' => array(
                        Chart::PERIOD_YEAR  => 'Année',
                        Chart::PERIOD_MONTH => 'Mois'
                    ),
                    'label' => 'Période du graphique',
                    'attr' => array('onchange'  => 'javascript:this.form.submit()')
                )
            );

        // Create the form used for grap configuration
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        return $this->render("TellawLeadsFactoryBundle:monitoring:dashboard_utm_page.html.twig", array(
            'form'  => $form->createView(),
            'alerteutil' => $this->get("alertes_utils"),
        ));

    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getUtmLinkedToFormAction ( $form_id ) {

        $entity = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Form")->find( $form_id );
        $utms = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Form")->getUtmLinkedToForm( $form_id );

        $utmsObjects = array();

        foreach ( $utms as $item ) {
            $result = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Form")->getStatisticsForUtmInForm( $item["utm"], $form_id );
            $utmsObjects[$result["transformRate"]] = $result;
        }

        krsort( $utmsObjects );

        return $this->render("TellawLeadsFactoryBundle:monitoring:utmsLinkedToFormWidget.html.twig", array(
            'entity'   => $entity,
            'utmsObjects' => $utmsObjects,
            'alerteutil' => $this->get("alertes_utils"),
        ));

    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getFormsInTypeWidgetAction ( $type_id ) {

        $entities = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:FormType")->getFormsInFormType( $type_id );

        $forms = array();

        foreach ( $entities as $form ) {
            $form = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->setStatisticsForId($form->getId());
            $forms[$form->transformRate] = $form;
        }

        krsort( $forms );

        return $this->render("TellawLeadsFactoryBundle:monitoring:formsInTypeWidget.html.twig", array(
            'forms'   => $forms,
            'alerteutil' => $this->get("alertes_utils"),
        ));
    }

    /**
     *
     * Controlleur dédié à la création des graphiques.
     * Il doit prendre en paramètres :
     * 1 ) $mode => FormType ou Form : Définit le type d'objet à afficher sur le graph.
     * 2 ) $objects => array : Tableau d'elements à afficher sur le graph. Attention si le mode est sur Form le tableau sera des objets Form et si mode est sur FormType,
     *     il sera alors un tableau d'objets FormType
     *
     * @Secure(roles="ROLE_USER")
     */
    public function chartDashboardAction($period=Chart::PERIOD_YEAR, $mode='FormType', $objects = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        /** @var $chart Tellaw\LeadsFactoryBundle\Utils\Chart */
        $chart = $this->get('chart');
        $chart->setPeriod($period);

        // Get Bookmarks of object's type FormType
        if( $mode == 'FormType' && $objects == null ){

            $query = $em->createQuery('SELECT f FROM TellawLeadsFactoryBundle:FormType f, TellawLeadsFactoryBundle:Bookmark b WHERE b.formType = f.id AND b.user ='.$user->getId());
            $formTypes = $query->getResult();
            $chart->setFormType($formTypes);

        // Get Bookmarks of object's type Form
        } else if ( $mode == 'Form' && $objects == null ) {

            $query = $em->createQuery('SELECT f FROM TellawLeadsFactoryBundle:Form f, TellawLeadsFactoryBundle:Bookmark b WHERE b.form = f.id AND b.user ='.$user->getId());
            $forms = $query->getResult();
            $chart->setForm($forms);

        // Get Array of objects of object's type FormType
        } else if ($mode == 'FormType' && $objects != null ) {

            $chart->setFormType ( $objects );

        // Get Array of objects of object's type Form
        } else if ( $mode == 'Form' && $objects != null ) {

            $chart->setForm ( $objects );

        // Throw exception for wrong state
        } else {
            throw new Exception ("Mode for graph is incorrect : ".$mode."/". implode ( '/', $objects ));
        }

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

    /**
     * @Secure(roles="ROLE_USER")
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

        return $this->render("TellawLeadsFactoryBundle:monitoring:measure.html.twig", array(
            'entities'  => $entities,
	        'alerteutil' => $this->get("alertes_utils"),
            'title'  => $title
        ));
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getAlertWidgetForTypeAction ( $type_id ) {

        $formTypeEntity = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->find($type_id);

        if ($formTypeEntity == null) throw new Exception ("FormType cannot be null");

        return $this->render("TellawLeadsFactoryBundle:monitoring:measureFormTypeItem.html.twig", array(
            'item'  => $formTypeEntity,
            'alerteutil' => $this->get("alertes_utils"),
        ));

    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getAlertWidgetForFormAction ( $form_id ) {

        $formEntity = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($form_id);

        if ($formEntity == null) throw new Exception ("Form cannot be null");

        return $this->render("TellawLeadsFactoryBundle:monitoring:measureFormItem.html.twig", array(
            'item'  => $formEntity,
            'alerteutil' => $this->get("alertes_utils"),
        ));

    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getTypeViewStatisticsAction ( $type_id ) {

        $formTypeEntity = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->setStatisticsForId($type_id);

        return $this->render("TellawLeadsFactoryBundle:monitoring:statisticsFormTypeItem.html.twig", array(
            'formType' => $formTypeEntity,
            'alerteutil' => $this->get("alertes_utils")
        ));

    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getFormViewStatisticsAction ( $form_id ) {

        $formEntity = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->setStatisticsForId($form_id);

        return $this->render("TellawLeadsFactoryBundle:monitoring:statisticsFormItem.html.twig", array(
            'form' => $formEntity,
            'alerteutil' => $this->get("alertes_utils")
        ));

    }

    /**
     * @Secure(roles="ROLE_USER")
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

        return $this->render("TellawLeadsFactoryBundle:monitoring:measure.html.twig", array(
            'entities'  => $entities,
            'alerteutil' => $this->get("alertes_utils"),
            'title'  => $title
        ));
    }

    /**
     * @route("/index", name="_monitoring_index")
     * @Secure(roles="ROLE_USER")
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

        return $this->render("TellawLeadsFactoryBundle:monitoring:index.html.twig", array(
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
        $template = "TellawLeadsFactoryBundle:monitoring:chart_bar.html.twig";

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