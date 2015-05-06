<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
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

/**
 * @Route("/search")
 */
class SearchController extends AbstractEntityController {
    /**
     * @Route("/index", name="_search_config")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {

        //$url="curl -XGET 127.0.0.1:9200/_cat/health?v";
        $searchUtils = $this->get("search.utils");

        $request = '_cluster/health?pretty=true';
        $response = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_GET , $request );

        if (trim($response) == ""){
            $status = false;
        } else {
            $status = true;
        }

        $request = '';
        $responseVersion = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_GET , $request );

        $request = '_stats/docs';
        $stats = $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_GET , $request );

        if (trim($response) != "") {
            $response = json_decode( $response );
        }

        if (trim($responseVersion) != "") {
            $responseVersion = json_decode( $responseVersion );
        }
//var_dump ($response);

        return $this->render(
	        'TellawLeadsFactoryBundle:Search:search_configuration.html.twig',
            array (
                'elasticResponse' => $response,
                'status' => $status,
                'stats' => $this->prettyPrint($stats),
                'version' => $responseVersion
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
     * @Route("/run", name="_search_run")
     * @Secure(roles="ROLE_USER")
     */
    public function runElasticAction () {


        if (! file_exists( "../vendor/tellaw/LeadsFactoryBundle/Tellaw/LeadsFactoryBundle/Search/bin/elasticsearch" )) {
            throw new Exception ("ElasticSearch binary not found");
        }
        $process = new Process('../vendor/tellaw/LeadsFactoryBundle/Tellaw/LeadsFactoryBundle/Search/bin/elasticsearch -d');
        $process->setTimeout(3600);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $messagesUtils = $this->container->get("messages.utils");
        //$messagesUtils->pushMessage( Messages::$_TYPE_SUCCESS, "Démarrage du service de recherche", $process->getOutput() );

        return $this->redirect($this->generateUrl('_search_config'));

    }

    /**
     * @Route("/shutdown", name="_search_stop")
     * @Secure(roles="ROLE_USER")
     */
    public function shutdownAction () {

        $request = '_shutdown';

        $searchUtils = $this->get("search.utils");
        $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_POST , $request );

        return $this->redirect($this->generateUrl('_search_config'));

    }

    /**
     * @Route("/deleteIndex", name="_search_delete_index")
     * @Secure(roles="ROLE_USER")
     */
    public function deleteIndex () {

        $request = '_all';

        $searchUtils = $this->get("search.utils");
        $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_DELETE , $request );

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

        $request = 'leadsfactory';


        $parameters =    '{
                        "mappings": {
                            "leads": {
                                "dynamic":      "strict",
                                "properties": {
                                    "_id":           { "type": "integer"},
                                    "id":           { "type": "integer"},
                                    "firstname":    { "type": "string"},
                                    "lastname":     { "type": "string"},
                                    "status":       { "type": "integer"},
                                    "exportdate":   { "type": "date"},
                                    "log":          { "type": "string"},
                                    "formTyped":    { "type": "integer"},
                                    "form":         { "type": "integer"},
                                    "utmcampaign":  { "type": "string"},
                                    "telephone":    { "type": "string"},
                                    "createdAt":    { "type": "date"},
                                    "email":        { "type": "string"},
                                    "client":       { "type": "integer"},
                                    "entreprise":   { "type": "integer"},
                                    "content":  {
                                        "type":     "object",
                                        "dynamic":  true
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
                                    "secure_key":   { "type": "integer"},
                                }
                            }
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
                                    "log":          { "type": "string"},
                                }
                            }
                        }
                    }';


        $searchUtils = $this->get("search.utils");
        $searchUtils->request ( ElasticSearchUtils::$PROTOCOL_PUT , $request, $parameters );


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
