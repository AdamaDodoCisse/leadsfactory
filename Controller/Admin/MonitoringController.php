<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tellaw\LeadsFactoryBundle\Entity\Bookmark;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Utils\Chart;
use \Tellaw\LeadsFactoryBundle\Utils\AlertUtils;

/**
 * @Route("/")
 */
class MonitoringController extends CoreController {

    public function __construct () {
        parent::__construct();

    }

    /**
     * @route("/dashboard", name="_monitoring_dashboard")
     * @Secure(roles="ROLE_USER")
     *//*
    public function dashboardAction(Request $request)
    {

        if (!$this->get("core_manager")->isMonitoringAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        // Logged User
        $user_id = $this->get('security.context')->getToken()->getUser()->getId();

        // Get All Types in the scope
        $types = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:FormType")->getFormsType();

        // Load bookmarked types for user
        $bookmarks = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Bookmark")->getTypesForUser( $user_id );

        return $this->render ("TellawLeadsFactoryBundle:monitoring:dashboard.html.twig", array(
            'types' => $types,
            'bookmarks' => $bookmarks
        ));
    }
*/
    /**
     * @route("/index", name="_monitoring_dashboard_forms")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardFormsAction(Request $request)
    {
        $data = array();

        if (!$this->get("core_manager")->isMonitoringAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

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

        // Get user scope
        $user_scope = $this->get('security.context')->getToken()->getUser()->getScope();

        // Get All Types of forms regardless the scope
        $raw_forms = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Form")->getForms();

        // Filter forms regarding scopes
        $forms = array();
        if ($user_scope) { // If there is a scope
            foreach($raw_forms as $f) {
                if ($f->getScope() == $user_scope)
                    $forms[] = $f;
            }
        } else { // If there is no scope
            $forms = $raw_forms;
        }

        // Load bookmarked forms for user
        $bookmarks = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Bookmark")->getFormsForUser( $user_id );
        $utils = $this->container->get('lf.utils');
        $utmForms = $this->get('leadsfactory.form_repository')->getStatisticsForUtmForms($forms, $utils);
        $utmBookmarks = $this->get('leadsfactory.form_repository')->getStatisticsForUtmBookmarks($forms, $bookmarks, $utils);

        // Load comparative statistics for forms
        $results = $this->get('leadsfactory.form_repository')->getStatisticsForForms($forms, $utils);

        $views = $results;
        unset ($views["NB_LEADS"]);
        unset ($views["id"]);

        $nbLeads = $results;
        unset ($views["PAGES_VIEWS"]);
        unset ($views["id"]);

        return $this->render ("TellawLeadsFactoryBundle:monitoring:dashboard_forms.html.twig", array(
            'form'          =>  $form->createView(),
            'bookmarks'     =>  $bookmarks,
            'forms'         =>  $forms,
            'utmForms'      =>  $utmForms,
            'utmBookmarks'  =>  $utmBookmarks,
            'nbviews'       =>  $views,
            'nbLeads'       =>  $nbLeads
        ));
    }

    /**
     * @route("/dashboard_type_page/{type_id}", name="_monitoring_dashboard_type_page")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardTypePageAction( Request $request, $type_id )
    {
        if (!$this->get("core_manager")->isMonitoringAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $entity = $this->get('leadsfactory.form_type_repository')->find( $type_id );

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

        // Get All Types in the scope
        $types = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:FormType")->getFormsType();

        return $this->render ("TellawLeadsFactoryBundle:monitoring:dashboard_type_page.html.twig", array(
            'form'  => $form->createView(),
            'entity' => $entity,
            'types' => $types
        ));

    }

    /**
     * @route("/dashboard_forms_page/{form_id}", name="_monitoring_dashboard_form_page")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardFormPageAction( Request $request, $form_id )
    {

        if (!$this->get("core_manager")->isMonitoringAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $entity = $this->get('leadsfactory.form_repository')->find( $form_id );

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

        // Get All Types in the scope
        $forms = $this->getDoctrine()->getRepository("TellawLeadsFactoryBundle:Form")->getForms();

        return $this->render("TellawLeadsFactoryBundle:monitoring:dashboard_form_page.html.twig", array(
            'form'  => $form->createView(),
            'entity'   => $entity,
            'alerteutil' => $this->get("alertes_utils"),
            'forms' => $forms
        ));
    }

    /**
     * @route("/dashboard_utm_page/{utm}", name="_monitoring_dashboard_utm_page")
     * @Secure(roles="ROLE_USER")
     */
    public function dashboardUtmPageAction( Request $request, $utm )
    {

        if (!$this->get("core_manager")->isMonitoringAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

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

        if (!$this->get("core_manager")->isMonitoringAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        $form_repository = $this->get('leadsfactory.form_repository');

        $entity = $form_repository->find( $form_id );
        $utms = $form_repository->getUtmLinkedToForm( $form_id );

        $utmsObjects = array();

        foreach ( $utms as $item ) {
            $result = $form_repository->getStatisticsForUtmInForm( $item["utm"], $form_id, $utils );
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
     * Check this function again !
     * @Secure(roles="ROLE_USER")
     */
    public function getStatsForFomPageAction ( $form_id ) {

        if (!$this->get("core_manager")->isMonitoringAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');
        $form_repository = $this->get('leadsfactory.form_repository');
        $utms = $form_repository->getUtmLinkedToForm( $form_id );

        $utmsObjects = array();
        foreach ( $utms as $item ) {
            $result = $form_repository->getStatisticsForUtmInForm( $item["utm"], $form_id, $utils );
            $utmsObjects[$result["transformRate"]] = $result;
        }

        foreach ($utmsObjects as $cpt => $utmObjects){
            $data_views[$cpt]['label'] =  $utmObjects['utm'];
            $data_views[$cpt]['value'] = $utmObjects['nbViews'];

            $data_leads[$cpt]['label'] =  $utmObjects['utm'];
            $data_leads[$cpt]['value'] = $utmObjects['nbLeads'];
        }

        return $this->render("TellawLeadsFactoryBundle:monitoring:pieChartInFormPage.html.twig", array(
            'data_nb_views' =>  $data_views,
            'data_nb_leads' =>  $data_leads
        ));
    }




    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getFormsInTypeWidgetAction ( $type_id ) {

        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        $entities = $this->get('leadsfactory.form_type_repository')->getFormsInFormType( $type_id );

        $forms = array();

        foreach ( $entities as $form ) {
            $form = $this->get('leadsfactory.form_repository')->setStatisticsForId($form->getId(), $utils);
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
     * 2 ) $objects => array : Tableau d'elements à afficher sur le graph.
     *      Attention si le mode est sur Form le tableau sera des objets Form et si mode est sur FormType,
     *      il sera alors un tableau d'objets FormType
     *
     * @Secure(roles="ROLE_USER")
     */
    public function chartDashboardAction($period=Chart::PERIOD_YEAR, $mode='FormType', $objects = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        /** @var $chart \Tellaw\LeadsFactoryBundle\Utils\Chart */
        $chart = $this->get('chart');
        $chart->setPeriod($period);

        if( $mode == 'FormType' && $objects == null ){ // Get Bookmarks of object's type FormType
            $query = $em->createQuery('SELECT f
                                      FROM TellawLeadsFactoryBundle:FormType f, TellawLeadsFactoryBundle:Bookmark b
                                      WHERE b.formType = f.id AND b.user ='.$user->getId());
            $formTypes = $query->getResult();
            $chart->setFormType($formTypes);

        } else if ( $mode == 'Form' && $objects == null ) { // Get Bookmarks of object's type Form
            $query = $em->createQuery('SELECT f
                                      FROM TellawLeadsFactoryBundle:Form f
                                        JOIN TellawLeadsFactoryBundle:Scope s WITH s.id = f.scope,
                                      TellawLeadsFactoryBundle:Bookmark b
                                      WHERE b.form = f.id AND b.user ='.$user->getId());
            $forms = $query->getResult();
            $chart->setForm($forms);

        } else if ($mode == 'FormType' && $objects != null ) { // Get Array of objects of object's type FormType
            $chart->setFormType ( $objects );

        } else if ( $mode == 'Form' && $objects != null ) { // Get Array of objects of object's type Form$
            $chart->setForm ( $objects );

        } else { // Throw exception for wrong state
            throw new \Exception ("Mode for graph is incorrect : ".$mode."/". implode ( '/', $objects ));
        }


        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        $chartData = $chart->loadChartData();

        $data = array(
            'chart_data'        => $chartData,
            'time_range'        => $chart->getTimeRange(),
            'chart_title'       => $chart->getChartTitle(),
            'normal_graphs'    => $chart->getNormalGraph($chartData), //indexes des graphes à afficher en mode 'ligne' (pour le combo chart)
            'user_preferences' => $utils->getUserPreferences()
        );

        return $this->render("TellawLeadsFactoryBundle:monitoring:chart_bar.html.twig", $data);
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

        $formTypeEntity = $this->get('leadsfactory.form_type_repository')->find($type_id);

        if ($formTypeEntity == null) {
            throw new \Exception ("FormType cannot be null");
        }

        /** @var AlertUtils $alertes_utils */
        $alertes_utils = $this->get("alertes_utils");
        $alertes_utils->setValuesForAlerts($formTypeEntity);

        return $this->render("TellawLeadsFactoryBundle:monitoring:measureFormTypeItem.html.twig", array(
            'item'  => $formTypeEntity,
        ));

    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getAlertWidgetForFormAction($form_id)
    {
        $formEntity = $this->get('leadsfactory.form_repository')->find($form_id);

        if ($formEntity == null) throw new \Exception ("Form cannot be null");

        /** @var AlertUtils $alertes_utils */
        $alertes_utils = $this->get("alertes_utils");
        $alertes_utils->setValuesForAlerts($formEntity);

        return $this->render("TellawLeadsFactoryBundle:monitoring:measureFormItem.html.twig", array(
            'item'  => $formEntity,
        ));
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getStatusForFormAction($form_id)
    {
        $formEntity = $this->get('leadsfactory.form_repository')->find($form_id);

        if ($formEntity == null) throw new \Exception ("Form cannot be null");

        /** @var AlertUtils $alertes_utils */
        $alertes_utils = $this->get("alertes_utils");
        $alertes_utils->setValuesForAlerts($formEntity);
        return new Response( $formEntity->yesterdayValue );

    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getTypeViewStatisticsAction($type_id)
    {

        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        $formTypeEntity = $this->get('leadsfactory.form_type_repository')->setStatisticsForId($type_id, $utils);

        return $this->render("TellawLeadsFactoryBundle:monitoring:statisticsFormTypeItem.html.twig", array(
            'formType' => $formTypeEntity,
        ));
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function getFormViewStatisticsAction($form_id)
    {
        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        $formEntity = $this->get('leadsfactory.form_repository')->setStatisticsForId($form_id, $utils);

        return $this->render("TellawLeadsFactoryBundle:monitoring:statisticsFormItem.html.twig", array(
            'form' => $formEntity,
        ));
    }


    /**
     * @Secure(roles="ROLE_USER")
     * TODO DEPRECATED : THIS FUNCTION IS NOW USELESS
     */
    public function getFormStatisticsValuesAction($form_id)
    {
        $results = array();
        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        $formEntity = $this->get('leadsfactory.form_repository')->setStatisticsForId($form_id, $utils);
        $results['nbViews'] = $formEntity->nbViews;
        $results['nbleads'] = $formEntity->nbLeads;
        $results['transformRate'] = $formEntity->transformRate;

        return $this->render("TellawLeadsFactoryBundle:monitoring:dashboardTableValues.html.twig", array(
            'form' => $formEntity,
        ));
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function measureAction($formType=null, $form=null)
    {
        $em = $this->getDoctrine()->getManager();

        if(!empty($form)){
            $form = $this->get('leadsfactory.form_repository')->find($form);
            $entities = array($form);
            $title = 'Formulaire '.$form->getName();
        }elseif(!empty($formType)){
            $entities = $this->get('leadsfactory.form_repository')->findByFormType($formType);
            $title = "Tous les formulaires du type sélectionné";
        }else{
            $entities = $this->get('leadsfactory.form_type_repository')->findAll();
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
     * @return Response
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
        $formTypes = $this->get('leadsfactory.form_type_repository')->findAll();
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

        $forms = $this->get('leadsfactory.form_repository')->findByFormType($form_type);

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
     * @param Request $request
     * @return Response
     * @internal param string $entity Form|FormType
     * @internal param int $id
     * @internal param bool $status
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