<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Tellaw\LeadsFactoryBundle\Entity\SearchResult;
use Tellaw\LeadsFactoryBundle\Entity\UserPreferences;

class ElasticSearchUtils {

    public static $PROTOCOL_PUT = "PUT";
    public static $PROTOCOL_POST = "POST";
    public static $PROTOCOL_GET = "XGET";
    public static $PROTOCOL_DELETE = "DELETE";

    public $baseUri = "http://localhost:9200/";


    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

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

        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $this->baseUri.$query);
        curl_setopt($ci, CURLOPT_PORT, '9200');
        curl_setopt($ci, CURLOPT_TIMEOUT, 10);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $protocol);
        if ($parameters) {
            curl_setopt($ci, CURLOPT_POSTFIELDS, $parameters);
        }
        $result = curl_exec($ci);

        if ($populate)
            return $this->populateObjectFromSearch( $result );
        else
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

