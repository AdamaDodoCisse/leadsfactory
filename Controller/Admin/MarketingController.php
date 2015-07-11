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
     * Start export
     *
     * @Route("/search", name="_marketing_index")
     * @Secure(roles="ROLE_USER")
     */
    public function searchAction(Request $request)
    {

        if ($request->get("query") != null) {
            var_dump ("request");

            $searchUtils = $this->get ("search.utils");

            /*
             * {"leadsfactory":{"mappings":{"form":{"properties":{"code":{"type":"string"},"description":{"type":"string"},"name":{"type":"string"},"scope":{"type":"long"},"script":{"type":"string"},"secure_key":{"type":"string"},"utmcampaign":{"type":"string"}}},"export":{"properties":{"created_at":{"type":"date","format":"dateOptionalTime"},"executed_at":{"type":"date","format":"dateOptionalTime"},"form_id":{"type":"long"},"lead_id":{"type":"long"},"log":{"type":"string"},"method":{"type":"string"},"scheduled_at":{"type":"date","format":"dateOptionalTime"},"status":{"type":"long"}}},"leads":{"properties":{"content":{"properties":{"acteur":{"type":"string"},"address":{"type":"string"},"clause":{"type":"string"},"cnilPartners":{"type":"string"},"cnilTi":{"type":"string"},"comment":{"type":"string"},"commentaire":{"type":"string"},"deja-client":{"type":"string"},"demande-rdv":{"type":"string"},"email":{"type":"string"},"etablissement":{"type":"string"},"evenement":{"type":"string"},"firstName":{"type":"string"},"fonction":{"type":"string"},"lastName":{"type":"string"},"livre-blanc":{"type":"string"},"nom-etablissement":{"type":"string"},"pack":{"type":"string"},"pays":{"type":"string"},"phone":{"type":"string"},"product_name":{"type":"string"},"product_sku":{"type":"string"},"profil":{"type":"string"},"redirect_url":{"type":"string"},"referrer_url":{"type":"string"},"salutation":{"type":"string"},"secteur-activite":{"type":"string"},"service":{"type":"string"},"thematique":{"type":"string"},"timestamp":{"type":"string"},"trackingOrigin":{"type":"string"},"twilio_validation":{"type":"string"},"type-etablissement":{"type":"string"},"utmcampaign":{"type":"string"},"utmcontent":{"type":"string"},"utmmedium":{"type":"string"},"utmsource":{"type":"string"},"ville":{"type":"string"},"ville_id":{"type":"string"},"ville_text":{"type":"string"},"wcb_type":{"type":"string"},"wk-cgv":{"type":"string"},"wk-partners":{"type":"string"},"zip":{"type":"string"}}},"createdAt":{"type":"date","format":"dateOptionalTime"},"email":{"type":"string"},"exportdate":{"type":"date","format":"dateOptionalTime"},"firstname":{"type":"string"},"form_id":{"type":"long"},"form_type_id":{"type":"long"},"id":{"type":"long"},"lastname":{"type":"string"},"log":{"type":"string"},"status":{"type":"long"},"telephone":{"type":"string"},"utmcampaign":{"type":"string"}}}}}}
             */
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

            var_dump ($leadsContent);

            $results = $searchUtils->searchQueryString( $request->get("query") );
            var_dump($results);

        } else {
            $results = null;
        }

        // https://www.elastic.co/guide/en/elasticsearch/guide/current/search-lite.html
        // https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#query-string-syntax

        return $this->render(
            'TellawLeadsFactoryBundle:marketing:index.html.twig',
            array(
                "results" => $results,

            )
        );

    }

}
