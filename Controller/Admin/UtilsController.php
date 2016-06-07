<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Shared\CoreManager;

/**
 * @Route("/utils")
 */
class UtilsController extends CoreController
{



    public function __construct () {
        parent::__construct();
    }

    /**
     * @Route("/licenceError", name="_security_licence_error")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function licenceAction(){

        $informations = CoreManager::getLicenceInfos();

        return $this->render('TellawLeadsFactoryBundle:Security:licenceError.html.twig', array(
            // last username entered by the user
            'information'         => $informations
        ));

    }

    /**
     * @Route("/licence", name="_security_licence")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function licenceErrorAction(){

        $informations = CoreManager::getLicenceInfos();
        return $this->render('TellawLeadsFactoryBundle:Security:licenceDetail.html.twig', array(
            // last username entered by the user
            'information'         => $informations
        ));

    }


    /**
     * @Route("/messages/{parentRoute}", name="_utils_messages")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function messagesAction (Request $request, $parentRoute) {

        /** @var Tellaw\LeadsFactoryBundle\Utils\Messages $messagesUtils */
        $messagesUtils = $this->container->get("messages.utils");
        $pooledMessages = $messagesUtils->pullMessages( $parentRoute );

        return $this->render('TellawLeadsFactoryBundle:Utils:messages.html.twig', array ("messages" => $pooledMessages));

    }

    /**
     * @Route("/preferences/settimeperiod", name="_utils_preferences_timeperiod")
     * @Secure(roles="ROLE_USER")
     */
    public function setTimeperiodPreferenceAction ( Request $request ) {


        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->get('lf.utils');

        /** @var Tellaw\LeadsFactoryBundle\Entity\UserPreferences $userPrefences */
        $userPrefences = $utils->getUserPreferences();

        $data = array(  'datemin' => $userPrefences->getDataPeriodMinDate() ,
                        'datemax' => $userPrefences->getDataPeriodMaxDate() ,
                        'zoom' => $userPrefences->getDataZoomOption(),
                        'type' => $userPrefences->getDataTypeOfGraph(),
                        'moyenne' => $userPrefences->getDataDisplayAverage(),
                        'total' => $userPrefences->getDataDisplayTotal(),
                        'period' => $userPrefences->getPeriod()
            );

        $formBuilder = $this->createFormBuilder($data);
        $formBuilder->setAction($this->generateUrl('_utils_preferences_timeperiod'));
        $formBuilder->setMethod('POST')

            ->add('period', 'choice', array(
                    'expanded' => true,
                    'label' => 'Période glissante (début de mois) ou pésronnalisée (custom)',
                    'choices' => array(

                        '7D' => '7 Jours',
                        '1M' => '1 Mois',
                        '3M' => '3 Mois',
                        '6M' => '6 Mois',
                        '1Y' => '1 an',
                        'custom' => 'Custom',
                        ),
                    'attr'   =>  array(
                        'class'   => 'graphadvanced periodConfiguration'
                    )
                )
            )

            ->add('datemin', 'date', array(
                    'label' => 'Date de début',
                    'widget'=>'single_text')
            )

            ->add('datemax', 'date', array(
                    'label' => 'Date de fin',
                    'widget'=>'single_text')
            )

            ->add('displaymode', 'choice', array(
                    'expanded' => true,
                    'label' => 'Paramétrage du graphique',
                    'choices' => array(
                        'nok' => 'Mode compact',
                        'ok' => 'Paramètres étendus',
                    ),
                    'data' => 'nok',
                    'attr'   =>  array(
                        'class'   => 'graphadvanced displayConfiguration',
                    )
                )
            )

            ->add('type', 'choice', array(
                    'label' => 'Type de graphique',
                    'choices' => array(
                        'bar' => 'Barre cumulatives',
                        'chart' => 'Courbes superposées'),
                    'attr'   =>  array(
                        'class'   => 'graphadvanced',
                    )
                )
            )
            ->add('zoom', 'choice', array(
                    'label' => 'Option de zoom',
                    'choices' => array(
                        'none'  => 'Aucun zoom',
                        'zoom' => 'Zoom par molette de souris',
                        'subgraph' => 'Zoom sur region'),
                    'attr'   =>  array(
                        'class'   => 'graphadvanced')
                    )
            )->add('moyenne', 'checkbox', array(
                    'label' => 'Afficher la moyenne',
                    'attr'   =>  array(
                        'class'   => 'graphadvanced'),
                    'required'    => false
                )
            )->add('total', 'checkbox', array(
                    'label' => 'Afficher le total',
                    'attr'   =>  array(
                        'class'   => 'graphadvanced'),
                    'required'    => false
                )
            )
            ->add('Valider', 'submit')
        ;


        // Create the form used for grap configuration
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $datemin = $form["datemin"]->getData();
            $datemax = $form["datemax"]->getData();

            $userPrefences->setDataPeriodMinDate ( $datemin );
            $userPrefences->setDataPeriodMaxDate ( $datemax );
            $userPrefences->setDataZoomOption ( $form["zoom"]->getData() );
            $userPrefences->setDataTypeOfGraph ( $form["type"]->getData() );
            $userPrefences->setDataDisplayAverage ( $form["moyenne"]->getData() );
            $userPrefences->setDataDisplayTotal ( $form["total"]->getData() );
            $userPrefences->setPeriod ( $form["period"]->getData() );

            $userPrefences = $utils->setUserPreferences( $userPrefences );

            $referer = $this->getRequest()->headers->get('referer');
            return $this->redirect($referer);

        }

        return $this->render ("TellawLeadsFactoryBundle:monitoring:timeperiod_form.html.twig", array(
            'form'  => $form->createView()
        ));

    }

    /**
     * @Route("/breadcrumb/{parentRoute}", name="_utils_breadcrumb")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function breadCrumbAction ( Request $request, $parentRoute ) {

        $sections = array();

        $displayBreadCrumb = true;

        $sections[] = array (   "name" => "Accueil", "url" => $this->get('router')->generate('_monitoring_dashboard_forms'));

        if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard_type_page")) == "_monitoring_dashboard_type_page") {

            $sections[] = array (   "name" => "Dashboard des groupements de formulaires", "url" => $this->get('router')->generate('_monitoring_dashboard'));
            $sections[] = array (   "name" => "Détail d'un groupement de formulaire", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard_form_page")) == "_monitoring_dashboard_form_page") {

            $sections[] = array (   "name" => "Dashboard des formulaires", "url" => $this->get('router')->generate('_monitoring_dashboard_forms'));
            $sections[] = array (   "name" => "Détail d'un formulaire", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard_forms")) == "_monitoring_dashboard_forms") {

            $sections[] = array (   "name" => "Dashboard des formulaires", "url" => "");
/*
        } else if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard")) == "_monitoring_dashboard") {

            $sections[] = array (   "name" => "Dashboard des groupements de formulaires", "url" => "");*/

        } else if (substr ($parentRoute, 0, strlen ("_form_list")) == "_form_list") {

            $sections[] = array (   "name" => "Liste des formulaires", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_form_new")) == "_form_new") {

            $sections[] = array (   "name" => "Liste des formulaires", "url" => $this->get('router')->generate('_form_list'));
            $sections[] = array (   "name" => "Création d'un formulaire", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_form_edit")) == "_form_edit") {

            $sections[] = array (   "name" => "Liste des formulaires", "url" => $this->get('router')->generate('_form_list'));
            $sections[] = array (   "name" => "Edition des formulaires", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_formType_list")) == "_formType_list") {

            $sections[] = array (   "name" => "Liste des groupements de formulaires", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_formType_new")) == "_formType_new") {

            $sections[] = array (   "name" => "Liste des groupements de formulaires", "url" => $this->get('router')->generate('_formType_list'));
            $sections[] = array (   "name" => "Création d'un groupement de formulaire", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_formType_edit")) == "_formType_edit") {

            $sections[] = array (   "name" => "Liste des groupements de formulaires", "url" => $this->get('router')->generate('_formType_list'));
            $sections[] = array (   "name" => "edition d'un groupement de formulaires", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_referenceList_list")) == "_referenceList_list") {

            $sections[] = array (   "name" => "Listes de références", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_referenceList_new")) == "_referenceList_new") {

            $sections[] = array (   "name" => "Listes de références", "url" => $this->get('router')->generate('_referenceList_list'));
            $sections[] = array (   "name" => "Création d'une liste de référence", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_referenceList_edit")) == "_referenceList_edit") {

            $sections[] = array (   "name" => "Listes de références", "url" => $this->get('router')->generate('_referenceList_list'));
            $sections[] = array (   "name" => "edition d'une liste de référence", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_leads_list")) == "_leads_list") {

            $sections[] = array (   "name" => "Géstion des leads", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_leads_edit")) == "_leads_edit") {

            $sections[] = array (   "name" => "Géstion des leads", "url" => $this->get('router')->generate('_leads_list'));
            $sections[] = array (   "name" => "Edition d'un LEAD", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_export_history")) == "_export_history") {

            $sections[] = array (   "name" => "Historique des leads exportés", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_users_list")) == "_users_list") {

            $sections[] = array (   "name" => "Listes des utilisateurs", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_users_new")) == "_users_new") {

            $sections[] = array (   "name" => "Listes des utilisateurs", "url" => $this->get('router')->generate('_users_list'));
            $sections[] = array (   "name" => "Création d'un utilisateur", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_users_edit")) == "_users_edit") {

            $sections[] = array (   "name" => "Listes des utilisateurs", "url" => $this->get('router')->generate('_users_list'));
            $sections[] = array (   "name" => "edition d'un utilisateur", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_scope_list")) == "_scope_list") {

            $sections[] = array (   "name" => "Scopes utilisateurs", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_scope_new")) == "_scope_new") {

            $sections[] = array (   "name" => "Scopes utilisateurs", "url" => $this->get('router')->generate('_scope_list'));
            $sections[] = array (   "name" => "Création d'un scope", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_scope_edit")) == "_scope_edit") {

            $sections[] = array (   "name" => "Scopes utilisateurs", "url" => $this->get('router')->generate('_scope_list'));
            $sections[] = array (   "name" => "edition d'un scope", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_mkg_segmentation_list")) == "_mkg_segmentation_list") {

            $sections[] = array (   "name" => "Gestion des exports de segments", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_kibana_dashboard_view")) == "_marketing_kibana_dashboard_view") {

            $sections[] = array (   "name" => "Gestion des exports de segments", "url" => $this->get('router')->generate('_marketing_kibana_exports_list'));
            $sections[] = array (   "name" => "Visualisation d'un ségment", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_kibana_dashboard_edit")) == "_marketing_kibana_dashboard_edit") {

            $sections[] = array (   "name" => "Gestion des exports de segments", "url" => $this->get('router')->generate('_marketing_kibana_exports_list'));
            $sections[] = array (   "name" => "Edition d'un ségment", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_kibana_dashboard_new")) == "_marketing_kibana_dashboard_new") {

            $sections[] = array (   "name" => "Gestion des exports de segments", "url" => $this->get('router')->generate('_marketing_kibana_exports_list'));
            $sections[] = array (   "name" => "Création d'un ségment", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_list_kibana_dashboards")) == "_marketing_list_kibana_dashboards") {

            $sections[] = array (   "name" => "Dashboards Marketing", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_kibana_exports_list")) == "_marketing_kibana_exports_list") {

            $sections[] = array (   "name" => "Gestion des segmentations", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_segment_view")) == "_marketing_segment_view") {

            $sections[] = array (   "name" => "Visualisation des réusltats du segment", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_segmentation_edit")) == "_marketing_segmentation_edit") {

            $sections[] = array (   "name" => "Gestion des segmentations", "url" => $this->get('router')->generate('_marketing_kibana_exports_list'));
            $sections[] = array (   "name" => "Edition des segmetations", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_marketing_segment_edit")) == "_marketing_segment_edit"
                    || substr ($parentRoute, 0, strlen ("_marketing_segment_add")) == "_marketing_segment_add"
                    || substr ($parentRoute, 0, strlen ("_marketing_segment_new_config")) == "_marketing_segment_new_config") {

            $sections[] = array (   "name" => "Génération des segments", "url" => "");

        }

        return $this->render('TellawLeadsFactoryBundle:Utils:breadcrumb.html.twig', array ("sections" => $sections, "route" => $parentRoute, "displayBreadCrumb" => $displayBreadCrumb));

    }

    /**
     * @Route("/version/{parentRoute}", name="_utils_version")
     * @Secure(roles="ROLE_USER")
     * @Cache(expires="tomorrow")
     * @template()
     */
    public function versionAction(Request $request, $parentRoute)
    {

        $version = implode ('',file('../vendor/tellaw/leadsfactory/Tellaw/LeadsFactoryBundle/version.txt'));
        list ($date, $version) = explode (";", $version);
        $date = str_replace (" CEST","",$date);
        return $this->render('TellawLeadsFactoryBundle:Utils:version.html.twig', array ("date" => $date, "version" => $version));

    }


    /**
     * @Route("/navigation/{parentRoute}", name="_utils_navigation")
     * @Secure(roles="ROLE_USER")
     * @Cache(expires="tomorrow")
     * @template()
     */
    public function navigationAction(Request $request, $parentRoute)
    {

        $sections = array(  "formulaires" => '0', "donnees" => '0', "users" => 0 );

        $mainRoute = $parentRoute;

        if (    substr ($mainRoute, 0, strlen ("_form_")) == "_form_"   ||
                substr ($mainRoute, 0, strlen ("_formType_")) == "_formType_" ||
                substr ($mainRoute, 0, strlen ("_referenceList_")) == "_referenceList_"   ) {

            $sections['formulaires'] = '1';

        } else if (    substr ($mainRoute, 0, strlen ("_leads_")) == "_leads_"  ) {

            $sections['donnees'] = '1';

        } else if (    substr ($mainRoute, 0, strlen ("_users_")) == "_users_"  ) {

            $sections['users'] = '1';

        }

        $json = null;
        if ($this->getUser()->getScope() != null) {
            $filePath = $this->get('kernel')->getRootDir()."/config/".$this->getUser()->getScope()->getCode()."-navigation.json";
            if (file_exists( $filePath )) {
                $json = json_decode(file_get_contents( $filePath ), true);
            }
        }

        return $this->render('TellawLeadsFactoryBundle:Utils:navigation.html.twig', array ("sections" => $sections, "route" => $mainRoute, "json" => $json));

    }

    /**
     * @Route("/streamtable/form/", name="_utils_streamtables_form")
     * @Secure(roles="ROLE_USER")
     */
    public function streamTableFormAction (Request $request) {

        // check parameters
        $q = $request->get("q");
        $limit = $request->get("limit",1000);
        $offset = $request->get("offset");

        $q = $this->getDoctrine()->getManager()
        ->createQueryBuilder()
            ->select('form')
            ->from('TellawLeadsFactoryBundle:Form','form')->setFirstResult($offset)->setMaxResults($limit);

        return $this->render('TellawLeadsFactoryBundle:Utils:streamtables_form.html.twig', array ( 'items' => new Paginator ($q) ));

    }

    /**
     * @Route("/streamtable/leads/", name="_utils_streamtables_leads")
     * @Secure(roles="ROLE_USER")
     */
    public function streamTableLeadsAction (Request $request) {

        // check parameters
        $q = $request->get("q");
        $limit = $request->get("limit",10);
        $offset = $request->get("offset");

        $q = $this->getDoctrine()->getManager()
            ->createQueryBuilder()
            ->select('leads')
            ->from('TellawLeadsFactoryBundle:Leads','leads')->setFirstResult($offset)->setMaxResults($limit);

        return $this->render('TellawLeadsFactoryBundle:Utils:streamtables_leads.html.twig', array ( 'items' => new Paginator ($q) ));

    }

}
