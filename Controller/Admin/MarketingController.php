<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Form\Type\MkgSegmentationType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Utils\ElasticSearchUtils;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;
use Tellaw\LeadsFactoryBundle\Entity;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/marketing")
 */
class MarketingController extends CoreController
{

    public function __construct () {
        parent::__construct();
    }

    /**
     *
     * Kibana page for browsing data
     *
     * @param Request $request
     * @Route("/kibana-browse", name="_marketing_kibana_index")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaBrowserAction (Request $request) {

        $preferences = $this->container->get ("preferences_utils");

        $search = $this->container->get ("search.utils");
        if (!$search->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $kibanaUrl = $preferences->getUserPreferenceByKey ( ElasticSearchUtils::$_PREFERENCE_SEARCH_KIBANA_URL );

        return $this->render(
            'TellawLeadsFactoryBundle:marketing:kibana-index.html.twig',
            array(
                "kibana_url" => $kibanaUrl
            )
        );
    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/dashboards", name="_marketing_list_kibana_dashboards")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaDashboardsAction ( Request $request ) {

        $search = $this->container->get ("search.utils");
        if (!$search->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $dashboards = $search->getKibanaDashboards();

        return $this->render(
            'TellawLeadsFactoryBundle:marketing:kibana-dashboards.html.twig',
            array(
                "dashboards" => $dashboards
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/dashboards/edit/{id}", name="_marketing_kibana_dashboard_edit")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaDashboardEditAction ( Request $request, $id ) {

        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $searches = $searchUtils->getKibanaSavedSearches();

        $formEntity = $this->get('leadsfactory.mkgsegmentation_repository')->find($id);
        $form = $this->createForm(
            new MkgSegmentationType($searches),
            $formEntity,
            array('method' => 'POST')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

        }

        $hits = "";
        $query = "";
        $fieldsToDisplayRaw = "";
        $fieldsToDisplay = array();
        $error = null;

        if($formEntity->getCode()) {

            try {

                $savedSearch = $searchUtils->getKibanaSavedSearch ( $formEntity->getCode(),$formEntity->getNbDays()  );

                if (!is_null( $savedSearch )) {
                    $query = $savedSearch->getQuery();
                    $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_POST , "/_search", $query );
                    $hits = $result->hits->hits;
                    $fieldsToDisplayRaw = implode (";",$savedSearch->getColumns());
                    $fieldsToDisplay = $savedSearch->getColumns();
                } else {
                    $error = "Chargement impossible de la recherche sauvegardée";
                }
            } catch ( \Exception $e) {
                $this->get("logger")->error ( $e->getMessage() );
                $error = "Chargement impossible de la recherche sauvegardée";
            }
        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:entity_edit.html.twig',
            array(
                "error" => $error,
                "query" => $query,
                "nbFieldsToDisplay" => count ($fieldsToDisplay),
                "fieldsToDisplayRaw" => $fieldsToDisplayRaw,
                "fieldsToDisplay" => $fieldsToDisplay,
                'searchResults' => $hits,
                'id' => $id,
                'form' => $form->createView(),
                'title' => "Edition d'un ségment"
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/dashboards/view/{id}", name="_marketing_kibana_dashboard_view")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaDashboardViewAction ( Request $request, $id ) {

        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $formEntity = $this->get('leadsfactory.mkgsegmentation_repository')->find($id);

        $result = "";
        $query = "";
        $fieldsToDisplayRaw = "";
        $fieldsToDisplay = array();
        if($formEntity->getCode()) {

            $savedSearch = $searchUtils->getKibanaSavedSearch ( $formEntity->getCode(),$formEntity->getNbDays()  );
            $query = $savedSearch->getQuery();
            $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_POST , "/_search", $query );

            $fieldsToDisplayRaw = implode (";",$savedSearch->getColumns());
            $fieldsToDisplay = $savedSearch->getColumns();
        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:entity_view.html.twig',
            array(
                "query" => $query,
                "nbFieldsToDisplay" => count ($fieldsToDisplay),
                "fieldsToDisplayRaw" => $fieldsToDisplayRaw,
                "fieldsToDisplay" => $fieldsToDisplay,
                'searchResults' => $result->hits->hits,
                'id' => $id,
                'title' => "Visualisation d'un ségment"
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/download/csv/{id}", name="_marketing_kibana_download_csv")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaDownloadCsv ( Request $request, $id ) {

        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }


        $formEntity = $this->get('leadsfactory.mkgsegmentation_repository')->find($id);

        $result = "";
        $query = "";
        $fieldsToDisplayRaw = "";
        $fieldsToDisplay = array();

        if($formEntity->getCode()) {

            $savedSearch = $searchUtils->getKibanaSavedSearch ( $formEntity->getCode(),$formEntity->getNbDays()  );
            $query = $savedSearch->getQuery();
            $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_POST , "/_search", $query );

            $fieldsToDisplayRaw = implode (";",$savedSearch->getColumns());
            $fieldsToDisplay = $savedSearch->getColumns();
        }


        $handle = fopen('php://temp', 'w');
        fputcsv( $handle, $fieldsToDisplay, ";", "\"", "\\" );
        $elements = $result->hits->hits;

        foreach ( $elements as $row)  {

            $leadsource = $row->_source;

            $content = array ();
            foreach ( $fieldsToDisplay as $fied ) {

                try {
                    if (trim($fied)!="") {
                        if (strstr($fied,"content.")) {
                            $headerrow = str_replace("content.","",$fied);
                            $obj = $leadsource->content;
                            $content[] = $obj->$headerrow;
                        } else {
                            $content[] = $leadsource->$fied;
                        }
                    }
                } catch (\Exception $e) {
                    $content[] = "";
                }

            }

            fputcsv( $handle, $content, ";", "\"", "\\" );

        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response =  new Response($content);
        $response->headers->set('content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename=leads_report.csv');

        return $response;

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/dashboards/new", name="_marketing_kibana_dashboard_new")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaDashboardNewAction ( Request $request ) {

        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $searches = $searchUtils->getKibanaSavedSearches();

        $form = $this->createForm(
            new MkgSegmentationType($searches),
            null,
            array('method' => 'POST')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_marketing_kibana_exports_list'));
        }
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:entity_edit.html.twig',
            array(
                'error' => '',
                'searchResults' => '',
                'form' => $form->createView(),
                'title' => "Création d'un Ségment"
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/dashboards/open/{id}", name="_marketing_kibana_dashboard_open")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaDashboardOpenAction ( Request $request, $id ) {

        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $preferences = $this->container->get ("preferences_utils");
        $kibanaUrl = $preferences->getUserPreferenceByKey ( ElasticSearchUtils::$_PREFERENCE_SEARCH_KIBANA_URL );

        return $this->render(
            'TellawLeadsFactoryBundle:marketing:kibana-dashboard.html.twig',
            array(
                "kibana_url" => $kibanaUrl."#/dashboard/".$id
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/dashboards/delete/{id}", name="_marketing_kibana_dashboard_delete")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaDashboardDeleteAction ( Request $request, $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->get('leadsfactory.mkgsegmentation_repository')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_marketing_kibana_exports_list'));

    }


    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/exports/list/{page}/{limit}/{keyword}", name="_marketing_kibana_exports_list")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaExportListAction ( $page=1, $limit=10, $keyword='' ) {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:MkgSegmentation', $page, $limit, $keyword, array ('user'=>$this->getUser()));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:entity_list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options'],
                'list'     => $list
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/error", name="_marketing_kibana_error")
     * @Secure(roles="ROLE_USER")
     */
    public function kibanaErrorAction ( ) {
        $this->get("logger")->error ( "KIBANA Process may not be running" );
        return $this->render(
            'TellawLeadsFactoryBundle:Utils:kibana_error.html.twig',
            array(

            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/search/error", name="_marketing_search_error")
     * @Secure(roles="ROLE_USER")
     */
    public function elasticSearchErrorAction ( ) {
        $this->get("logger")->error ( "elasticSearch Process may not be running" );
        return $this->render(
            'TellawLeadsFactoryBundle:Utils:kibana_error.html.twig',
            array(

            )
        );

    }

}
