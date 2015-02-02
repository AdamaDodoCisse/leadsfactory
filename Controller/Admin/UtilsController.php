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

/**
 * @Route("/utils")
 */
class UtilsController extends AbstractLeadsController
{

    /**
     * @Route("/messages/{parentRoute}", name="_utils_messages")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function messagesAction (Request $request, $parentRoute) {

        /** @var $messagesUtils Tellaw\LeadsFactoryBundle\Utils\Messages */
        $messagesUtils = $this->container->get("messages.utils");
        $pooledMessages = $messagesUtils->pullMessages( $parentRoute );

        return $this->render('TellawLeadsFactoryBundle:Utils:messages.html.twig', array ("messages" => $pooledMessages));

    }

    /**
     * @Route("/breadcrumb/{parentRoute}", name="_utils_breadcrumb")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function breadCrumbAction ( Request $request, $parentRoute ) {

        $sections = array();

        $sections[] = array (   "name" => "Accueil", "url" => $this->get('router')->generate('_monitoring_dashboard'));

        if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard_type_page")) == "_monitoring_dashboard_type_page") {

            $sections[] = array (   "name" => "Dashboard des groupements de formulaires", "url" => $this->get('router')->generate('_monitoring_dashboard'));
            $sections[] = array (   "name" => "Détail d'un groupement de formulaire", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard_form_page")) == "_monitoring_dashboard_form_page") {

            $sections[] = array (   "name" => "Dashboard des formulaires", "url" => $this->get('router')->generate('_monitoring_dashboard_forms'));
            $sections[] = array (   "name" => "Détail d'un formulaire", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard_forms")) == "_monitoring_dashboard_forms") {

            $sections[] = array (   "name" => "Dashboard des formulaires", "url" => "");

        } else if (substr ($parentRoute, 0, strlen ("_monitoring_dashboard")) == "_monitoring_dashboard") {

            $sections[] = array (   "name" => "Dashboard des groupements de formulaires", "url" => "");

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

        }

        return $this->render('TellawLeadsFactoryBundle:Utils:breadcrumb.html.twig', array ("sections" => $sections, "route" => $parentRoute));

    }

    /**
     * @Route("/navigation/{parentRoute}", name="_utils_navigation")
     * @Secure(roles="ROLE_USER")
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

        return $this->render('TellawLeadsFactoryBundle:Utils:navigation.html.twig', array ("sections" => $sections, "route" => $mainRoute));

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
