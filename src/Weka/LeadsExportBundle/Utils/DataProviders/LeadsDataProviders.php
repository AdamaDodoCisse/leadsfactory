<?php
namespace Weka\LeadsExportBundle\Utils\DataProviders;

class LeadsDataProviders extends AbstractDataProvider{

    public function getDatas ( $args ) {

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

        return $request;

    }

}