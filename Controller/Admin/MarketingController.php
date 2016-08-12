<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Form\Type\MkgSegmentationType;
use Tellaw\LeadsFactoryBundle\Form\Type\MkgSegmentType;
use Tellaw\LeadsFactoryBundle\Form\Type\SegmentConfigType;
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
use Tellaw\LeadsFactoryBundle\Utils\SegmentUtils;

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
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
     * @param $segmentation_id
     * @return mixed
     * @Route("/kibana/segment/new_config/{segmentation_id}", name="_marketing_segment_new_config")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentAddConfigAction($segmentation_id) {
        $error = null;
        $formView = null;

        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $segmentation = $formEntity = $this->get('leadsfactory.mkgsegmentation_repository')->find($segmentation_id);
        $config = $segmentation->getConfig();

        if (!$config) {
            $error = "Aucune configuration trouvée";
        } else {
            $config = json_decode($config, true);
            $form = $this->createForm(
                new SegmentConfigType($config, $this->generateUrl('_marketing_segment_add', array('segmentation_id'=>$segmentation_id)))
            );
            $formView = $form->createView();
        }
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:segment_edit.html.twig',
            array(
                'action' => 'add',
                'segmentation_id' => $segmentation_id,
                'error' => $error,
                'form' => $formView,
                'title' => "Création d'un nouveau segment (Etape 1)",
            )
        );
    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/segment/add/{segmentation_id}", name="_marketing_segment_add")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentAddAction(Request $request, $segmentation_id) {
        $error = null;
        $data = $request->request->all();
        $formview = null;
        $filter = null;

        if ($request->getMethod() == 'POST' && isset($data['segmentConfig'])) {
            $data = $data['segmentConfig'];
            unset($data['_token']);
            $form = $this->createForm(new MkgSegmentType(json_encode($data), $segmentation_id));
            $formView = $form->createView();

            return $this->render(
                'TellawLeadsFactoryBundle:entity/Marketing:segment_edit.html.twig',
                array(
                    'action' => 'add',
                    'segmentation_id' => $segmentation_id,
                    'error' => $error,
                    'form' => $formView,
                    'title' => "Création d'un nouveau segment (Etape 2)",
                )
            );
        } else if($request->getMethod() == 'POST' && isset($data['mkgSegment'])) {
            $data = $data['mkgSegment'];
            unset($data['_token']);
            $form = $this->createForm(new MkgSegmentType($data['filter'], $segmentation_id));

            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($form->getData());
                $em->flush();
                $id =  $form->getData()->getId();
            }
            return $this->redirect($this->generateUrl('_marketing_segment_edit', array('id'=>$id)));
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/segment/edit/{id}", name="_marketing_segment_edit")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentEditAction(Request $request, $id) {
        $formEntity = $this->get('leadsfactory.mkgsegment_repository')->find($id);
        $form = $this->createForm(
            new MkgSegmentType($formEntity->getFilter(), $formEntity->getSegmentation()),
            $formEntity,
            array('method' => 'POST')
        );
        $formView = $form->createView();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
            return $this->redirect($this->generateUrl('_marketing_segment_edit', array('id' => $id)));
        }
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:segment_edit.html.twig',
            array(
                'action' => 'edit',
                'id' => $id,
                'segmentation_id' => $formEntity->getSegmentation(),
                'error' => '',
                'form' => $formView,
                'title' => "Editer un segment",
            )
        );
    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/dashboards/edit/{id}", name="_marketing_segmentation_edit")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentationEditAction( Request $request, $id  ) {
        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $searches = $searchUtils->getKibanaSavedSearches();

        $formEntity = $this->get('leadsfactory.mkgsegmentation_repository')->find($id);
        $segments = $this->get('leadsfactory.mkgsegment_repository')->findBy(array('segmentation'=>$id));
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

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:segmentation_edit.html.twig',
            array(
                'error' => '',
                'id' => $id,
                'form' => $form->createView(),
                'segments' => $segments,
                'title' => "Edition d'une segmentation",
                'segmentation' => $formEntity
            )
        );
    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/segment/view/{id}", name="_marketing_segment_view")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentViewAction(Request $request, $id) {
        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $segment = $this->get('leadsfactory.mkgsegment_repository')->find($id);
        $segmentation = $this->get('leadsfactory.mkgsegmentation_repository')->find($segment->getSegmentation());

        $result = "";
        $query = "";
        $fieldsToDisplayRaw = "";
        $fieldsToDisplay = array();
        if($segmentation->getQueryCode()) {
            $savedSearch = $searchUtils->getKibanaSavedSearch ( $segmentation->getQueryCode());
            $query = $savedSearch->getQuery();

            SegmentUtils::addFilterConfig($query, $segment);
            $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_POST , "/_search?size=10000&from=0", $query );

            $fieldsToDisplayRaw = implode (";",$savedSearch->getColumns());
            $fieldsToDisplay = $savedSearch->getColumns();
        }
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Marketing:segment_view.html.twig',
            array(
                "query" => $query,
                "nbFieldsToDisplay" => count ($fieldsToDisplay),
                "fieldsToDisplayRaw" => $fieldsToDisplayRaw,
                "fieldsToDisplay" => $fieldsToDisplay,
                'searchResults' => $result->hits->hits,
                'id' => $id,
                'title' => "Visualisation d'un ségment",
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     * @Route("/kibana/segment/download/csv/{id}", name="_marketing_segment_download_csv")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentDownloadCsvAction ( Request $request, $id ) {
        $searchUtils = $this->get ("search.utils");
        if (!$searchUtils->isKibanaAlive()) {
            return $this->redirectToRoute('_marketing_kibana_error');
        }

        $segment = $this->get('leadsfactory.mkgsegment_repository')->find($id);
        $segmentation = $this->get('leadsfactory.mkgsegmentation_repository')->find($segment->getSegmentation());

        $result = "";
        $query = "";
        $fieldsToDisplayRaw = "";
        $fieldsToDisplay = array();
        if($segmentation->getQueryCode()) {
            $savedSearch = $searchUtils->getKibanaSavedSearch ( $segmentation->getQueryCode() );
            $query = $savedSearch->getQuery();

            SegmentUtils::addFilterConfig($query, $segment);
            $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_POST , "/_search?size=10000&from=0", $query );

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
     * @Route("/kibana/dashboards/new", name="_marketing_segmentation_new")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentationNewAction ( Request $request ) {

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
            'TellawLeadsFactoryBundle:entity/Marketing:segmentation_edit.html.twig',
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
    public function mkgDashboardOpenAction ( Request $request, $id ) {

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
    public function mkgSegmentationDeleteAction ( Request $request, $id ) {

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
     * @Route("/kibana/segment/delete/{id}", name="_marketing_segment_delete")
     * @Secure(roles="ROLE_USER")
     */
    public function mkgSegmentDeleteAction ( Request $request, $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->get('leadsfactory.mkgsegment_repository')->find($id);
        $segmentation_id = $object->getSegmentation();

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_marketing_segmentation_edit', array('id'=>$segmentation_id)));

    }


    /**
     * @param int $page
     * @param int $limit
     * @param string $keyword
     * @return mixed
     * @internal param Request $request
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
            'TellawLeadsFactoryBundle:entity/Marketing:segmentation_list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options'],
                'list'     => $list
            )
        );

    }

    /**
     * @return mixed
     * @internal param Request $request
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
     * @return mixed
     * @internal param Request $request
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
