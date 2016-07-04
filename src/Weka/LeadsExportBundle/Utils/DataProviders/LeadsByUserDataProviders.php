<?php
namespace Weka\LeadsExportBundle\Utils\DataProviders;

/**
 * Class LeadsByUserDataProviders
 *
 * This provider is intented to extrract informations about the number of leads for users inside the search engine.
 * It can take two parameters :
 *
 * - code : The code object to use (email for user, ID for team)
 * - bu : true | false to specify if the content is related to a team or a user.
 *
 * @package Weka\LeadsExportBundle\Utils\DataProviders
 */
class LeadsByUserDataProviders extends AbstractDataProvider{

    private $isBu = null;
    private $id = null;
    private $code = null;
    private $members = null;

    public function getDatas ( $args ) {



        if (array_key_exists( "bu" , $args )) {

            // Load the correct BU
            $this->code = $args["code"];

            // This is a BU
            $this->isBu= true;
            $this->id = $args["buId"];

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

        } else {

            // If is not a BU, we'll load a team
            $this->isBu = false;

        }

        return $this->getValues( $args );

    }

    protected function formatResult ( $result ) {

        $output = array();
        foreach ( $result->aggregations->my_agg->buckets as $bucket ) {
            $output[ $bucket->key ] = array ( "value" => $bucket->doc_count );
            foreach ( $bucket->mysecond_agg->buckets as $secondBucket ) {
                $output[$bucket->key][$secondBucket->key] = $secondBucket->doc_count;
            }
            // Calculate TX for user
            // This is an instant TX calculation, including the very last leads.

            if ( array_key_exists( "gagne", $output[ $bucket->key ] ) && $output[ $bucket->key ]["gagne"] > 0 ) {

                $gagne = $output[ $bucket->key ]["gagne"];

                if (array_key_exists( "perdu", $output[ $bucket->key ] )) {
                    $perdu = $output[ $bucket->key ]["perdu"];
                } else {
                    $perdu = 0;
                }

                $output[ $bucket->key ]["tx"] = round(100 * $gagne / ( $gagne + $perdu ));

            }
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
                      \"scopeId\": \"4\"
                    }
                  },
                  \"filter\": [
                    ".$this->getBuFilter()."
                    { \"range\": { \"createdAt\": { \"gte\": \"2016-01-01\" } } }
                  ]
                }
              },
              \"size\": 0,
              \"aggs\": {
                \"my_agg\": {
                      \"terms\" : {
                        \"field\" : \"userEmail\"
                      },
                  \"aggs\": {
                    \"mysecond_agg\" : {
                      \"terms\" : {
                        \"field\" : \"workflowStatus\"
                      }
                    }
                  }
                }
              }
            }
        ";

        return $request;

    }

    protected function getBuFilter () {

        $filter = "";

        if ($this->isBu) {
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
            $filter = "{ \"terms\" : { \"userEmail\": [".$emails."] } },";
        }

        return $filter;

    }

}