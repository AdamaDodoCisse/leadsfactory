<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
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
     * @Route("/segmentation/list/{page}/{limit}/{keyword}", name="_mkg_segmentation_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        $list = $this->getList ('TellawLeadsFactoryBundle:MkgSegmentation', $page, $limit, $keyword, array ('user'=>$this->getUser()));

        return $this->render(
            'TellawLeadsFactoryBundle:marketing/entity:segmentation_list.html.twig',
            array(
            )
        );

    }


    /**
     * Start export
     *
     * @Route("/search", name="_marketing_index")
     * @Secure(roles="ROLE_USER")
     */
    public function searchAction(Request $request)
    {

        $searchUtils = $this->get ("search.utils");
        $results = $searchUtils->getIndexFields();
        $results = json_decode( $results, true );
        $fields = $results["leadsfactory"]["mappings"]["leads"]["properties"];

        $leadsContent = array();

        foreach ( $fields["content"]["properties"] as $key => $element ) {
            $leadsContent[] = "content.".$key;
        }

        unset ($fields["content"]);
        foreach ( $fields as $key=>$element ) {
            $leadsContent[] = $key;
        }

        //var_dump ($leadsContent);

        $q = $request->get("q");
        $field = $request->get("field");
        $fieldsToDisplayRaw = $request->get ("fieldstodisplay");
        $fieldsToDisplay = explode(";",$fieldsToDisplayRaw);

        $results = null;

        if ( $q != null) {
            //var_dump ("request");

/*
 * /72/WW/WBZ0001
 * http://local.dev/weka-leadsfactory/web/app_dev.php/admin/marketing/search?field=content.utmcampaign&q=%2F72%2FWW%2FWBZ0001&fieldstodisplay=id%3Bcontent.firstName%3Bcontent.lastName
 */

            $results = $searchUtils->searchQueryString( $q,$field );
            //var_dump($results);

        } else {
            $results = null;
        }

        // https://www.elastic.co/guide/en/elasticsearch/guide/current/search-lite.html
        // https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#query-string-syntax

        return $this->render(
            'TellawLeadsFactoryBundle:marketing:index.html.twig',
            array(
                "results" => $results,
                "leadsfields" => $leadsContent,
                "q" => $q,
                "field" => $field,
                "fieldsToDisplayRaw" => $fieldsToDisplayRaw,
                "fieldsToDisplay" => $fieldsToDisplay,
                "results" => json_decode($results)
            )
        );

    }

}
