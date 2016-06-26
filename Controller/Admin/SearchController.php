<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Utils\ElasticSearchUtils;
use Tellaw\LeadsFactoryBundle\Utils\LFUtils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Process\Process;
use Tellaw\LeadsFactoryBundle\Utils\PreferencesUtils;

/**
 * @Route("/search")
 */
class SearchController extends CoreController {

    public static $_SEARCH_URL_AND_PORT_ELASTICSEARCH_PREFERENCE = "SEARCH_URL_AND_PORT_ELASTICSEARCH";

    public function __construct () {

        PreferencesUtils::registerKey( ElasticSearchUtils::$_SEARCH_URL_AND_PORT_ELASTICSEARCH_PREFERENCE,
            "Url to elastic search",
            PreferencesUtils::$_PRIORITY_REQUIRED );

        parent::__construct();
    }

    /**
     * Index page of search section in ADMIn
     * @Route("/index", name="_search_config")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {

        $preferences = $this->get ("preferences_utils");
        $searchEngineUrlAndPort = $preferences->getUserPreferenceByKey ( SearchController::$_SEARCH_URL_AND_PORT_ELASTICSEARCH_PREFERENCE );

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $request = '';
        $response = $this->get("search.utils")->request ( ElasticSearchUtils::$PROTOCOL_GET , $request );

        if (!is_object($response)){
            $status = false;
        } else {
            $status = true;
        }

        if (is_null($response) != "") {
            $response = json_decode( $response );
        }

        return $this->render(
	        'TellawLeadsFactoryBundle:Search:search_configuration.html.twig',
            array (
                'elasticResponse' => $response,
                'status' => $status,
                'searchEngineUrl' => $searchEngineUrlAndPort
            )
        );

    }

    function prettyPrint( $json )
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ( $in_escape ) {
                $in_escape = false;
            } else if( $char === '"' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                    case '{': case '[':
                    $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
                }
            } else if ( $char === '\\' ) {
                $in_escape = true;
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
        }

        return $result;
    }

    /**
     * @Route("/deleteIndex", name="_search_delete_index")
     * @Secure(roles="ROLE_USER")
     */
    public function deleteIndex () {

        $sr = $this->get('leadsfactory.scope_repository');
        $scope_list = $sr->getAll();

        foreach ($scope_list as $s) {

            $request = '/leadsfactory-'.$s['s_code'];
            $searchUtils = $this->get("search.utils");
            $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_DELETE , $request );

        }

        return $this->redirect($this->generateUrl('_search_config'));

    }

    /**
     * @Route("/createIndex", name="_search_create_index")
     * @Secure(roles="ROLE_USER")
     */
    public function createIndex () {

        $this->createLeadsIndex();
        $this->createStatisticIndex();

        return $this->redirect($this->generateUrl('_search_config'));

    }

    private function createLeadsIndex () {

        $parameters =    '{
                        "mappings": {
                            "leads": {
                                "dynamic":      true,
                                "dynamic_templates": [
                                                { "contentfields": {
                                                      "match":              "*",
                                                      "match_mapping_type": "string",
                                                      "mapping": {
                                                          "type": "string",
                                                          "index": "not_analyzed"
                                                      }
                                                }}
                                            ],
                                "properties": {
                                    "id":           { "type": "integer","index": "not_analyzed"},
                                    "firstname":    { "type": "string","index": "not_analyzed"},
                                    "lastname":     { "type": "string","index": "not_analyzed"},
                                    "status":       { "type": "integer"},
                                    "exportdate":   { "type": "date"},
                                    "log":          { "type": "string","index": "not_analyzed"},
                                    "formTyped":    { "type": "integer"},
                                    "form":         { "type": "integer"},
                                    "utmcampaign":  { "type": "string","index": "not_analyzed"},
                                    "telephone":    { "type": "string","index": "not_analyzed"},
                                    "createdAt":    { "type": "date"},
                                    "email":        { "type": "string","index": "not_analyzed"},
                                    "client":       { "type": "integer"},
                                    "entreprise":   { "type": "integer"},
                                    "formTypeName": { "type": "string","index": "not_analyzed"},
                                    "ipAdress":     { "type": "string","index": "not_analyzed"},
                                    "userAgent":    { "type": "string","index": "not_analyzed"},
                                    "scopeName":    { "type": "string","index": "not_analyzed"},
                                    "formName":     { "type": "string","index": "not_analyzed"},
                                    "userId":       { "type": "string","index": "not_analyzed"},
                                    "userFirstName":{ "type": "string","index": "not_analyzed"},
                                    "userLastName": { "type": "string","index": "not_analyzed"},
                                    "userEmail":    { "type": "string","index": "not_analyzed"},
                                    "workflowStatus":{ "type": "string","index": "not_analyzed"},
                                    "workflowType":  { "type": "string","index": "not_analyzed"},
                                    "workflowTheme": { "type": "string","index": "not_analyzed"},
                                    "content":  {
                                        "type":     "object",
                                        "dynamic":  true
                                    }
                                }
                            }
                        }
                    }';

        $sr = $this->get('leadsfactory.scope_repository');
        $scope_list = $sr->getAll();

        foreach ($scope_list as $scope) {

            $request = '/leadsfactory-'.$scope['s_code'];
            $searchUtils = $this->get("search.utils");
            $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_PUT , $request, $parameters );

        }

    }

    private function createStatisticIndex () {

        $parameters =    '{
                        "mappings": {
                            "statistic": {
                                "dynamic":      true,
                                "dynamic_templates": [
                                                { "contentfields": {
                                                      "match":              "*",
                                                      "match_mapping_type": "string",
                                                      "mapping": {
                                                          "type": "string",
                                                          "index": "not_analyzed"
                                                      }
                                                }}
                                            ],
                                "properties": {
                                    "id":       { "type": "integer","index": "not_analyzed"},
                                    "code":     { "type": "string","index": "not_analyzed"},
                                    "name":     { "type": "string","index": "not_analyzed"},
                                    "label":    { "type": "string","index": "not_analyzed"},
                                    "value":    { "type": "integer"},
                                    "createdAt":{ "type": "date"},
                                }
                            }
                        }
                    }';

        $sr = $this->get('leadsfactory.scope_repository');
        $scope_list = $sr->getAll();

        foreach ($scope_list as $scope) {

            $request = '/leadsfactory-'.$scope['s_code'];
            $searchUtils = $this->get("search.utils");
            $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_PUT , $request, $parameters );

        }

    }

}
