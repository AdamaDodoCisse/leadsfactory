<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Tellaw\LeadsFactoryBundle\Entity\KibanaSearch;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\SearchResult;
use Tellaw\LeadsFactoryBundle\Shared\SearchShared;

class ElasticSearchUtils extends SearchShared
{

    public static $PROTOCOL_PUT = "PUT";
    public static $PROTOCOL_POST = "POST";
    public static $PROTOCOL_GET = "XGET";
    public static $PROTOCOL_DELETE = "DELETE";

    //public static $_PREFERENCE_SEARCH_PATH_TO_ELASTICSEARCH = "SEARCH_BINARY_PATH";
    public static $_SEARCH_URL_AND_PORT_ELASTICSEARCH_PREFERENCE = "SEARCH_URL_AND_PORT_ELASTICSEARCH";

    public static $_PREFERENCE_SEARCH_KIBANA_ENABLE = "SEARCH_KIBANA_ENABLE";
    public static $_PREFERENCE_SEARCH_KIBANA_URL = "SEARCH_KIBANA_URL";
    //public static $_PREFERENCE_SEARCH_KIBANA_BINARY_PATH = "SEARCH_KIBANA_BINARY_PATH";
    public static $_PREFERENCE_SEARCH_KIBANA_INDEX_NAME = "SEARCH_KIBANA_INDEX_NAME";

    //public $baseUri = "http://localhost:9200/";

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    private $logger;


    public function __construct()
    {

        PreferencesUtils::registerKey(ElasticSearchUtils::$_SEARCH_URL_AND_PORT_ELASTICSEARCH_PREFERENCE,
            "Url to elastic search",
            PreferencesUtils::$_PRIORITY_REQUIRED);

        PreferencesUtils::registerKey(ElasticSearchUtils::$_PREFERENCE_SEARCH_KIBANA_ENABLE,
            "Set to true to activate Kibana or false to disable. By Default set to false",
            PreferencesUtils::$_PRIORITY_OPTIONNAL);

        PreferencesUtils::registerKey(ElasticSearchUtils::$_PREFERENCE_SEARCH_KIBANA_URL,
            "Url to Kibana application",
            PreferencesUtils::$_PRIORITY_OPTIONNAL);

        PreferencesUtils::registerKey(ElasticSearchUtils::$_PREFERENCE_SEARCH_KIBANA_INDEX_NAME,
            "Index name of the kibana index",
            PreferencesUtils::$_PRIORITY_OPTIONNAL);

    }

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    /**
     *
     * This method is used to check if Kibana server is responding.
     *
     * @return bool
     */
    public function isKibanaAlive()
    {
        $preferences = $this->container->get("preferences_utils");
        $baseUri = $preferences->getUserPreferenceByKey(ElasticSearchUtils::$_PREFERENCE_SEARCH_KIBANA_URL);
        if (@file($baseUri)) {
            return true;
        }

        return false;
    }

    /**
     *
     * This method is used to check is elastic search server is responding.
     *
     * @return bool
     */
    public function isElasticSearchAlive()
    {
        $preferences = $this->container->get("preferences_utils");
        $baseUri = $preferences->getUserPreferenceByKey(ElasticSearchUtils::$_SEARCH_URL_AND_PORT_ELASTICSEARCH_PREFERENCE);
        if (@file($baseUri)) {
            return true;
        }

        return false;
    }

    /**
     * Load saved search from Kibana
     *
     */
    public function getKibanaSavedSearches()
    {

        $query = "
        {
          \"query\": {
            \"match\": {
              \"_type\": \"search\"
            }
          },
          \"aggs\": {}
        }
        ";
        $savedSearch = $this->request(ElasticSearchUtils::$PROTOCOL_GET, "/.kibana/search/_search?", $query);

        if (!is_null($savedSearch->hits))
            $hits = $savedSearch->hits;
        else
            $hits = array();

        $results = array();

        foreach ($hits->hits as $hit) {
            $results[$hit->_id] = $hit->_source->title;
        }

        return $results;

    }

    /**
     * Load saved search from Kibana
     *
     * @param $searchId : Code of search in KIBANA
     * @param $nbDays : Number of days to include in search response
     *
     */
    public function getKibanaSavedSearch($searchId, $nbDays = 60)
    {

        $query = "
        {
          \"query\": {
            \"match\": {
              \"_id\": \"" . $searchId . "\"
            }
          },
          \"aggs\": {}
        }
        ";

        $savedSearch = $this->request(ElasticSearchUtils::$PROTOCOL_GET, "/.kibana/search/_search?q=_id:" . $searchId, null);

        try {

            $result = $savedSearch->hits->hits;
            $result = $result[0];

            $kibanaSearch = new KibanaSearch();
            $kibanaSearch->setId($result->_id);
            $kibanaSearch->setTitle($result->_source->title);
            $kibanaSearch->setDescription($result->_source->description);
            $kibanaSearch->setHits($result->_source->hits);
            $kibanaSearch->setColumns($result->_source->columns);
            $kibanaSearch->setSort($result->_source->sort);
            $kibanaSearch->setQuery($this->formatKibanaSavedObject($result->_source->kibanaSavedObjectMeta->searchSourceJSON, $nbDays));
            $kibanaSearch->setVersion($result->_source->version);

        } catch (\Exception $e) {

            $this->logger->error($e->getMessage());

            return null;

        }

        return $kibanaSearch;

    }

    /**
     * Method used to reformat data inside the request of Kibana.
     * - Remove of Highlights
     * - Add of date range filter
     *
     * @param $strSavedSearch
     */
    private function formatKibanaSavedObject($strSavedSearch, $nbDays)
    {

        if ($nbDays == "" || $nbDays == "0") {
            $nbDays = "360";
        }

        $jsonSavedSearch = json_decode($strSavedSearch, true);

        // Remove of highlights
        unset ($jsonSavedSearch["highlight"]);

        // Unset Index
        unset ($jsonSavedSearch["index"]);

        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $nbDays . 'D'));
        $firstDay = $date->format('Y-m-d');

        $rangeFilter = array(
            "range" => array("createdAt" => array("gte" => $firstDay, "lte" => "now", "time_zone" => "+1:00"))
        );

        $jsonSavedSearch["filter"] = $rangeFilter;

        $query = json_encode($jsonSavedSearch);

        return $query;


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
    public function request($protocol, $query, $parameters = null, $populate = false)
    {

        $preferences = $this->container->get("preferences_utils");
        $baseUri = $preferences->getUserPreferenceByKey(ElasticSearchUtils::$_SEARCH_URL_AND_PORT_ELASTICSEARCH_PREFERENCE);

        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri . $query);
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
            $result = json_decode($result);
            if (method_exists($result, "error")) {
                echo("ERROR : " . $baseUri . $query);
            }
        }

        if ($populate)
            return $this->populateObjectFromSearch($result);
        else {
            return $result;
        }
    }

    /**
     * @param $fields Array of fields to represent Lead object
     * @param $scopeId
     * @return mixed|SearchResult
     */
    public function indexLeadObject($fields, $scopeId)
    {

        $fields = $this->getIndexableLeadsObject($fields);

        return $this->request(ElasticSearchUtils::$PROTOCOL_PUT, "/leadsfactory-" . $scopeId . "/leads/" . $fields["id"], json_encode($fields), false);
    }

    /**
     * @param $fields Array of fields to represent Lead object
     * @param $scopeId
     * @return mixed|SearchResult
     */
    public function indexStatisticObject($fields, $scopeId)
    {
        //$fields = $this->getIndexableLeadsObject( $fields );
        $result = $this->request(ElasticSearchUtils::$PROTOCOL_PUT, "/leadsfactory-" . $scopeId . "/statistic/" . $fields["id"], json_encode($fields), false);
    }

    /**
     *
     * Method used to apply formating to leads content before sending it to the search engine
     *
     * @param $fields Array of fields to index.
     * @return mixed Array of formated fields for search engine index
     */
    public function getIndexableLeadsObject($fields)
    {

        try {
            if (array_key_exists("exportdate", $fields) && $fields["exportdate"] != null) {
                $fields["exportdate"] = $fields["exportdate"]->format("c");
            }
            if (array_key_exists("createdAt", $fields) && $fields["createdAt"] != null) {
                $fields["createdAt"] = $fields["createdAt"]->format("c");
            }
        } catch (\Exception $e) {
            echo($e->getMessage());
        }

        return $fields;
    }

    /**
     *
     * Method used to load the list of dashboard defined in KIBANA
     *
     * @return mixed Array of elements
     */
    public function getKibanaDashboards()
    {
        $request = "";
        $dashboards = $this->request(ElasticSearchUtils::$PROTOCOL_GET, "/.kibana/dashboard/_search?q=*", $request);

        return $dashboards->hits->hits;
    }


    /**
     *
     * Method used to populate objects with search engine content.
     *
     * @param $json
     * @return SearchResult
     * @throws \Exception
     */
    public function populateObjectFromSearch($json)
    {

        $em = $this->container->get("doctrine")->getManager();

        $content = json_decode($json);

        $results = new SearchResult();

        $results->setTook($content->took);
        $results->setMaxScore($content->hits->max_score);
        $results->setTotal($content->hits->total);

        foreach ($content->hits->hits as $hit) {

            if ($hit->_type == 'leads') {
                $object = new Leads();
            } else {
                throw new \Exception ("Type is not available for ORM mapping : " . $hit->_type);
            }

            $results->addResult($object->populateFromSearch($hit->_source, $em));

        }

        return $results;
    }

}

