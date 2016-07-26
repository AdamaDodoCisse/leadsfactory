<?php
namespace Weka\LeadsExportBundle\Utils\DataProviders;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class TransformRateDataProviders
 *
 * This provider is intented to extrract informations about tranformation rate inside the search engine.
 * It can take two parameters :
 *
 * - code : The code object to use (email for user, ID for team)
 * - bu : true | false to specify if the content is related to a team or a user.
 *
 * @package Weka\LeadsExportBundle\Utils\DataProviders
 *
 */
class TransformRateDataProviders extends AbstractDataProvider{

    private $isBu = null;
    private $id = null;
    private $code = null;

    public function getDatas ( $args ) {

        // Load the correct BU
        if (array_key_exists( "bu" , $args )) {

            $this->code = $args["code"];

            // This is a BU
            $this->isBu= true;
            $this->id = $args["code"];

        } else {

            // If is not a BU, we'll load a team
            $this->isBu = false;

            $user = $this->_container->get("doctrine")->getRepository("TellawLeadsFactoryBundle:Users")->findOneByEmail($args["code"]);
            $this->id = $user->getId();

        }

        return $this->getValues( $args );

    }

    protected function formatResult ( $result ) {

        $i = 0;
        $len = count ($result->aggregations->my_agg->buckets);
        $output = "";

        $timeSeries = array();
        $column = array();

        foreach ( $result->aggregations->my_agg->buckets as $bucket ) {

            if ($bucket->av_agg->value != null) {
                $value = $bucket->av_agg->value;
            } else {
                $value = 0;
            }

            $timeSeries[] = $bucket->key_as_string;
            $column[] = $value;

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
        ['Taux de transformation', ".implode (",",$column)."],
        ";

        return $output;

    }

    /**
     *
     * Les données de taux de transformation sont enregistrées avec les codes suivants :
     *
     * CODE : comundi_tx_by_user (attention ce code est pour les taux par personnes et par BU)
     * Identifiant des données :
     *
     * user-tx-{ID} : Taux de transformation pour l'utilisateur
     * team-tx-{ID} : Taux de transformation pour l'equipe.
     * user-gagne-{ID} : Nombre de lead declarés gagné à ce jour
     * user-perdu-{ID} : Nombre de lead declarés perdus à ce jour
     * team-gagne-{ID} : Nombre de lead declarés gagné à ce jour
     * team-perdu-{ID} : Nombre de lead declarés perdus à ce jour
     *
     * @return string
     */
    protected function getRequest () {

        $codeToFind = "";

        if ($this->isBu) {
            $codeToFind .= "team-tx-";
        }  else {
            $codeToFind .= "user-tx-";
        }

        $codeToFind .= $this->id;

        $request = "
            {
              \"query\": {
                \"bool\": {
                  \"must\": {
                    \"match\": {
                      \"name\": \"".$codeToFind."\"
                    }
                  },
                  \"filter\" : [
            
                      {\"range\": {\"createdAt\": {\"gte\":\"2016-01-01\"}}}
                      
                    ]
                    
                }
              },
              \"size\":0,
              \"aggs\": {
                \"my_agg\": {
                  \"date_histogram\": {
                    \"field\": \"createdAt\",
                    \"min_doc_count\" : 0,
                    \"format\": \"yyyy-MM-dd\",
                    \"interval\": \"day\"
                  },
                  \"aggs\" : {
                    \"av_agg\": {
                       \"avg\" : { \"field\" : \"value\" } 
                    }
                  }
                }
              }
            }
        ";

        return $request;

    }
}