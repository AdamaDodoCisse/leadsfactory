<?php
namespace Weka\LeadsExportBundle\Utils\DataProviders;

use Symfony\Component\Config\Definition\Exception\Exception;

class TxTransformDataProviders extends AbstractDataProvider{

    private $isBu = false;
    private $bu = null;
    private $members = array();

    public function getDatas ( $args ) {

        if (array_key_exists( "bu" , $args )) {

            // is a BU Graph
            $this->bu = $args["bu"];
            $email = $args["email"];

            // Checking if user is related to a team
            $json = null;
            if ($email != null) {
                $filePath = $this->_container->get('kernel')->getRootDir()."/config/comundi-team-description.json";
                if (file_exists( $filePath )) {
                    $jsonArray = json_decode(file_get_contents( $filePath ), true);
                }
            }

            if ( $jsonArray ) {

                if ( $email != null && $email != "" ) {
                    if (array_key_exists($email, $jsonArray )) {
                        foreach ( $jsonArray[$email] as $teamDetail ) {
                            $this->members =  $teamDetail["members"];
                        }
                    } else {
                        throw new \Exception("Email is not a team Manager");
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
        foreach ( $result->aggregations->my_agg->buckets as $bucket ) {
            if ($i == $len - 1) {
                $output .= "['" . $bucket->key . "', " . $bucket->doc_count . "]";
            } else {
                $output .= "['" . $bucket->key . "', " . $bucket->doc_count . "],";
            }
            $i++;
        }

        return $output;

    }

    protected function getRequest () {

        $request = "
            {
              \"query\": {
                \"bool\": {
                  \"must\": {
                    \"match\": {
                      \"scopeId\" : \"4\"
                    }
                  },
                  \"filter\" : [
                      ".$this->getBuFilter()."
                      {\"range\": {\"createdAt\": {\"gte\":\"2016-01-01\"}}}
                      
                    ]
                    
                }
              },
              \"size\":0,
              \"aggs\": {
                    \"my_agg\": {
                      \"terms\": {
                        \"field\": \"workflowStatus\"
                      }
                    }
              }
            }
        ";

        var_dump($request);

        return $request;

    }

    protected function getBuFilter () {

        $filter = "";

        if ($this->bu) {
            $emails = "";
            $i = 0;
            $len = count ($this->members);
            foreach ( $this->members as $member) {
                $emails.= "\"".$member."\"";
                if ( $i == $len-1) {
                    $emails.="";
                } else {
                    $emails .= ",";
                }
                $i++;
            }

            $filter = "
            
            {
              \"terms\" : {
                          \"userEmail\": [
                          ".$emails."
                            ]
              }
            },
            
            ";

        }

        return $filter;

    }

}