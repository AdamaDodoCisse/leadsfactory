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

    public static $_SEARCH_URL_AND_PORT__ELASTICSEARCH_PREFERENCE = "SEARCH_URL_AND_PORT__ELASTICSEARCH";

    public function __construct () {

        PreferencesUtils::registerKey( SearchController::$_SEARCH_URL_AND_PORT__ELASTICSEARCH_PREFERENCE,
                            "Url and port of the search service",
                            PreferencesUtils::$_PRIORITY_OPTIONNAL);


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
        $searchEngineUrlAndPort = $preferences->getUserPreferenceByKey ( SearchController::$_SEARCH_URL_AND_PORT__ELASTICSEARCH_PREFERENCE );

        //$url="curl -XGET 127.0.0.1:9200/_cat/health?v";
        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $request = '';
        $searchUtils = $this->get("search.utils");
        $response = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_GET , $request );

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
                                    "_id":           { "type": "integer","index": "not_analyzed"},
                                    "id":           { "type": "integer"},
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
                                    "content":  {
                                        "type":     "object",
                                        "dynamic":  true,
                                        "index": "analyzed"
                                    }
                                }
                            },
                            "form" : {
                                "dynamic":      "strict",
                                "properties": {
                                    "_id":          { "type": "integer"},
                                    "id":           { "type": "integer"},
                                    "type_id":      { "type": "integer"},
                                    "name":         { "type": "string"},
                                    "description": { "type": "string"},
                                    "code":        { "type": "string"},
                                    "utmcampaign": { "type": "string"},
                                    "scope":       { "type": "integer"},
                                    "script":      { "type": "string"},
                                    "secure_key":   { "type": "integer"}
                                }
                            },
                            "export" : {
                                "dynamic":      "strict",
                                "properties": {
                                    "_id":          { "type": "integer"},
                                    "id":           { "type": "integer"},
                                    "lead_id":      { "type": "integer"},
                                    "form_id":      { "type": "integer"},
                                    "method":       { "type": "string"},
                                    "created_at":   { "type": "date"},
                                    "scheduled_at": { "type": "date"},
                                    "executed_at":  { "type": "date"},
                                    "status":       { "type": "integer"},
                                    "log":          { "type": "string"}
                                }
                            }
                        }
                    }';

        $sr = $this->get('leadsfactory.scope_repository');
        $scope_list = $sr->getAll();

        foreach ($scope_list as $scope) {

            $request = '/leadsfactory-'.$scope['s_code'];
            $searchUtils = $this->get("search.utils");
            $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_PUT , $request, $parameters );

        }

    }

    /**
     * @Route("/indexDocuments", name="_search_index_documents")
     * @Secure(roles="ROLE_USER")
     */
    public function indexDocuments () {

        $dbUser = $this->container->getParameter('database_user');
        $dbPwd = $this->container->getParameter('database_password');
        $dbName = $this->container->getParameter('database_name');
        $dbHost = $this->container->getParameter('database_host');
        $dbPort = $this->container->getParameter('database_port');

        if (trim ($dbPort) == "")
            $dbPort = "3306";


        $this->indexLeadsJdbc( $dbUser, $dbPwd, $dbName, $dbHost, $dbPort );
        $this->indexFormJdbc( $dbUser, $dbPwd, $dbName, $dbHost, $dbPort );
        $this->indexExportJdbc( $dbUser, $dbPwd, $dbName, $dbHost, $dbPort );

        return $this->redirect($this->generateUrl('_search_config'));

    }

    /**
     * Method used to index LEADS Objects
     */
    private function indexLeadsJdbc ( $dbUser, $dbPwd, $dbName, $dbHost, $dbPort ) {

        $request = '_river/jdbc_river_leads/_meta';
        $searchUtils = $this->get("search.utils");
        $parameters =   '{
                            "type" : "jdbc",
                            "schedule" : "0 0-59 0-23 ? * *",
                            "jdbc" : {
                                "url" : "jdbc:mysql://'.$dbHost.':'.$dbPort.'/'.$dbName.'",
                                "user" : "'.$dbUser.'",
                                "password" : "'.$dbPwd.'",
                                "index" : "leadsfactory",
                                "type" : "leads",
                                "sql" : "select l.id _id, l.* from Leads l"
                            }
                        }';
        $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_PUT , $request, $parameters );

    }

    private function indexFormJdbc ( $dbUser, $dbPwd, $dbName, $dbHost, $dbPort ) {

        $request = '_river/jdbc_river_form/_meta';
        $searchUtils = $this->get("search.utils");
        $parameters =   '{
                            "type" : "jdbc",
                            "schedule" : "0 0-59 0-23 ? * *",
                            "jdbc" : {
                                "url" : "jdbc:mysql://'.$dbHost.':'.$dbPort.'/'.$dbName.'",
                                "user" : "'.$dbUser.'",
                                "password" : "'.$dbPwd.'",
                                "index" : "leadsfactory",
                                "type" : "form",
                                "sql" : "select l.id _id, l.name, l.description, l.script, l.code, l.utmcampaign, l.secure_key, l.scope from Form l"
                            }
                        }';
        $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_PUT , $request, $parameters );

    }

    private function indexExportJdbc ( $dbUser, $dbPwd, $dbName, $dbHost, $dbPort ) {

        $request = '_river/jdbc_river_export/_meta';
        $searchUtils = $this->get("search.utils");
        $parameters =   '{
                            "type" : "jdbc",
                            "schedule" : "0 0-59 0-23 ? * *",
                            "jdbc" : {
                                "url" : "jdbc:mysql://'.$dbHost.':'.$dbPort.'/'.$dbName.'",
                                "user" : "'.$dbUser.'",
                                "password" : "'.$dbPwd.'",
                                "index" : "leadsfactory",
                                "type" : "export",
                                "sql" : "select l.id _id, l.lead_id, l.form_id, l.method, l.created_at, l.scheduled_at, l.executed_at, l.status, l.log from Export l"
                            }
                        }';
        $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_PUT , $request, $parameters );

    }

    /**
     * @Route("/testSearch", name="_search_test")
     * @Secure(roles="ROLE_USER")
     */
    public function testSearchAction () {

        $query = "leadsfactory/_search";

        $parameters = '{ "query": {
                                "match": {
                                    "content.test": "toto"
                                }
                            }
                        }';

        $searchUtils = $this->get("search.utils");
        $result = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_POST , $query, $parameters, true );

        var_dump ($result);
        die();

    }

}
