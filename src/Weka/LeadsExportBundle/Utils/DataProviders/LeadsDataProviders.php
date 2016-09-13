<?php
namespace Weka\LeadsExportBundle\Utils\DataProviders;

class LeadsDataProviders extends AbstractDataProvider{

    private $code = null;
    private $bu = null;
    private $members = array();

    public function getDatas ( $args ) {
var_dump($args);
        if (array_key_exists( "bu" , $args )) {

            var_dump("BU filter");

            // is a BU Graph
            $this->bu = $args["bu"];
            $this->code = $args["code"];

            // Checking if user is related to a team
            $json = null;
            if ($this->code != null) {
                $filePath = $this->_container->get('kernel')->getRootDir()."/config/comundi-team-description.json";
                if (file_exists( $filePath )) {
                    $jsonArray = json_decode(file_get_contents( $filePath ), true);
                }
            }

            if ( $jsonArray ) {
                if ( $this->code != null && $this->code != "" ) {
                    foreach ( $jsonArray as $manager ) {
                        foreach ( $manager as $team ) {
                            if ( $team["id"] == $this->code ) {
                                $this->members = $team["members"];
                            }
                        }
                    }
                }
            }

        }

        return $this->getValues( $args );

    }

    protected function formatResult ( $result ) {

        $i = 0;
        $len = count ($result->aggregations->my_agg->buckets);
        $output = "";

        $timeSeries = array();
        $columns = array();

        foreach ( $result->aggregations->my_agg->buckets as $bucket ) {

            $timeSeries[] = $bucket->key_as_string;
            $column[] = $bucket->doc_count;

        }

        $seriesX = "";
        foreach ($timeSeries as $timeItem) {
            if ($i == $len - 1) {
                $seriesX .= "'" . $timeItem . "'";
            } else {
                $seriesX .= "'" . $timeItem . "',";
            }
            $i++;
        }

        $output = "
        ['x', ".$seriesX."],
        ['Leads', ".implode (",",$column)."],
        ";

        return $output;

    }

    protected function getRequest () {

        $request = "
            {
              \"query\": {
                \"bool\": {
                  \"must\": {
                    \"match\": {
                      \"scopeId\": \"4\"
                    }
                  },
                  \"filter\": [
                    ".$this->getBuFilter()."
                    {
                      \"range\": {
                        \"createdAt\": {
                          \"gte\": \"2016-01-01\"
                        }
                      }
                    }
                  ]
                }
              },
              \"size\": 0,
              \"aggs\": {
                \"my_agg\": {
                  \"date_histogram\": {
                    \"field\": \"createdAt\",
                    \"min_doc_count\" : 0,
                    \"format\": \"yyyy-MM-dd\",
                    \"interval\": \"day\"
                  }
                }
              }
            }
        ";

        var_dump($request);

        return $request;

    }

    protected function getBuFilter ()
    {

        $filter = "";

        if ($this->bu) {
            $emails = "";
            $i = 0;
            $len = count($this->members);
            foreach ($this->members as $member) {
                $emails .= "\"" . $member . "\"";
                if ($i == $len - 1) {
                    $emails .= "";
                } else {
                    $emails .= ",";
                }
                $i++;
            }

            $filter = "
            
            {
              \"terms\" : {
                          \"userEmail\": [
                          " . $emails . "
                            ]
              }
            },
            
            ";

        }

        return $filter;
    }

}