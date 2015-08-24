<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Tellaw\LeadsFactoryBundle\Entity\SearchResult;
use Tellaw\LeadsFactoryBundle\Entity\UserPreferences;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Tellaw\LeadsFactoryBundle\Shared\SearchShared;

class ElasticSearchUtils extends SearchShared {

    public static $PROTOCOL_PUT = "PUT";
    public static $PROTOCOL_POST = "POST";
    public static $PROTOCOL_GET = "XGET";
    public static $PROTOCOL_DELETE = "DELETE";

    public static $_PREFERENCE_SEARCH_PATH_TO_ELASTICSEARCH = "SEARCH_BINARY_PATH";
    public static $_SEARCH_URL_AND_PORT__ELASTICSEARCH_PREFERENCE = "SEARCH_URL_AND_PORT__ELASTICSEARCH";

    public static $_PREFERENCE_SEARCH_KIBANA_ENABLE = "SEARCH_KIBANA_ENABLE";
    public static $_PREFERENCE_SEARCH_KIBANA_URL = "SEARCH_KIBANA_URL";
    public static $_PREFERENCE_SEARCH_KIBANA_BINARY_PATH = "SEARCH_KIBANA_BINARY_PATH";
    public static $_PREFERENCE_SEARCH_KIBANA_INDEX_NAME = "SEARCH_KIBANA_INDEX_NAME";

    //public $baseUri = "http://localhost:9200/";

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function __construct () {
        PreferencesUtils::registerKey( ElasticSearchUtils::$_PREFERENCE_SEARCH_PATH_TO_ELASTICSEARCH, "Path to the binary file of elastic search", PreferencesUtils::$_PRIORITY_REQUIRED, null, true );
        PreferencesUtils::registerKey( ElasticSearchUtils::$_SEARCH_URL_AND_PORT__ELASTICSEARCH_PREFERENCE, "Url to elastic search", PreferencesUtils::$_PRIORITY_REQUIRED, null, true );


    }

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     *
     * Low level request to the search engine
     *
     * @param $protocol
     * @param $query
     * @param null $parameters
     * @param bool $populate
     * @return mixed|SearchResult
     * @throws \Exception
     */
    public function request ( $protocol, $query, $parameters = null, $populate = false ) {

        $preferences = $this->container->get ("preferences_utils");
        $baseUri = $preferences->getUserPreferenceByKey ( ElasticSearchUtils::$_SEARCH_URL_AND_PORT__ELASTICSEARCH_PREFERENCE );

        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri.$query);
        curl_setopt($ci, CURLOPT_PORT, '9200');
        curl_setopt($ci, CURLOPT_TIMEOUT, 10);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $protocol);
        if ($parameters) {
            curl_setopt($ci, CURLOPT_POSTFIELDS, $parameters);
        }
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ci);
        $error = curl_error($ci);
        curl_close($ci);

        if ($result) {
            $result = json_decode( $result);
            if (method_exists($result,"error")) {
                echo ("ERROR : ".$baseUri.$query);
                var_dump ($result);die();
            }
        }

        if ($populate)
            return $this->populateObjectFromSearch( $result );
        else
            return $result;
    }

    /**
     * Method used to start the ElasticSearch Process
     * @return bool
     * @throws Exception
     */
    public function start ()
    {

        // get preference used to find elastic search
        $preferences = $this->container->get ("preferences_utils");
        $searchEnginePath = $preferences->getUserPreferenceByKey ( ElasticSearchUtils::$_PREFERENCE_SEARCH_PATH_TO_ELASTICSEARCH );

        if ( trim ($searchEnginePath) == "" ) {
            throw new \Exception ("Configuration option not found : ".ElasticSearchUtils::$_PREFERENCE_SEARCH_PATH_TO_ELASTICSEARCH);
        }

        if (file_exists( $searchEnginePath )) {
            $process = new Process( $searchEnginePath . ' -d');
        } else {
            throw new \Exception ("ElasticSearch binary not found");
        }

        $input = new StringInput("");
        $output = new BufferedOutput();

        $process->setTimeout(3600);
        $process->run(null, $output );

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $output;

    }

    /**
     * @param $fields Array of fields to represent Lead object
     */
    public function indexLeadObject ( $fields, $scopeId, $leadObject = null ) {

        $exportDate = "";
        $createdAt = "";

        if ($leadObject != null) {

            die();

        } else {

            $data = array();
            if ($fields["exportdate"] != "") {
                $exportDate = \DateTime::createFromFormat('Y-m-j H:i:s', $fields["exportdate"]);
                $data["exportdate"] = $exportDate->format("c");
            }
            if ($fields["createdAt"] != "") {
                $createdAt = \DateTime::createFromFormat('Y-m-j H:i:s', $fields["createdAt"]);
                $data["createdAt"] = $createdAt->format("c");
            }

            $data["id"] = $fields["id"];
            $data["_id"] = $fields["id"];
            $data["firstname"] = ($fields["firstname"]);
            $data["lastname"] = ($fields["lastname"]);
            $data["status"] = $fields["status"];
            $data["log"] = $fields["log"];
            $data["formTyped"] = $fields["form_type_id"];
            $data["form"] = $fields["form_id"];
            $data["utmcampaign"] = $fields["utmcampaign"];
            $data["telephone"] = $fields["telephone"];
            $data["email"] = $fields["email"];
            $data["client"] = $fields["client_id"];
            $data["entreprise"] = $fields["entreprise_id"];
            $data["scope"] = $scopeId;
            $data["content"] = json_decode($fields["content"]);

        }
        $response = $this->request( ElasticSearchUtils::$PROTOCOL_PUT, "/leadsfactory-".$scopeId."/leads/".$fields["id"], json_encode($data), false );

        unset ($data);
        unset ($exportDate);
        unset ($createdAt);

        //var_dump ($response);

    }

    public function getKibanaDashboards () {

        $request = "";

        $dashboards = $this->request( ElasticSearchUtils::$PROTOCOL_GET, "/.kibana/dashboard/_search?q=*", $request );

        //var_dump( $dashboards->hits->hits );

        return $dashboards->hits->hits;
    }


    /**
     * I believe bellow methods are all deprecated due to KIBANA USAGE
     */

    /**
     *
     * Basic search function
     *
     * @param $key
     * @param $value
     * @return mixed|SearchResult
     */
    public function searchQueryString ( $q, $field ) {

        $query = "_search";

        $parameters = '{"query":{"bool":{"must":[{"query_string":{"query":"'.$field.':\"'.$q.'\""}}],"must_not":[],"should":[]}},"from":0,"size":10,"sort":[],"facets":{}}';
        $result = $this->request ( ElasticSearchUtils::$PROTOCOL_POST , $query, $parameters );

        return $result;

    }

    public function getIndexFields ( ) {

        $query = "leadsfactory/_mapping";

        $parameters = '';

        $result = $this->request( ElasticSearchUtils::$PROTOCOL_GET , $query, $parameters );

        return $result;


    }

    /**
     *
     * Basic search function
     *
     * @param $key
     * @param $value
     * @return mixed|SearchResult
     */
    public function search ( $key, $value ) {

        $query = "leadsfactory/_search";

        $parameters = '{ "query": {
                                "match": {
                                    "'.$key.'": "'.$value.'"
                                }
                            }
                        }';

        $result = $this->request ( ElasticSearchUtils::$PROTOCOL_POST , $query, $parameters );

        return $result;

    }

    /**
     *
     * Method used to populate objects with search engine content.
     *
     * @param $json
     * @return SearchResult
     * @throws \Exception
     */
    public function populateObjectFromSearch ( $json ) {

        $em = $this->container->get("doctrine")->getManager();

        var_dump ( $json );
        $content = json_decode( $json );

        $results = new SearchResult();

        $results->setTook( $content->took );
        $results->setMaxScore( $content->hits->max_score );
        $results->setTotal( $content->hits->total );

        foreach ($content->hits->hits as $hit) {

          //var_dump ($hit);

            if ($hit->_type == 'leads') {
                $object = new Leads();
            } else {
                throw new \Exception ("Type is not available for ORM mapping : ".$hit->_type);
            }

            $results->addResult( $object->populateFromSearch( $hit->_source, $em ) );

        }

        return $results;
    }

}

