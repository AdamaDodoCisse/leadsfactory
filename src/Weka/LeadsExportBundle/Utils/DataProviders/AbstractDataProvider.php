<?php
namespace Weka\LeadsExportBundle\Utils\DataProviders;

class AbstractDataProvider {

    private $_searchUtils = null;
    protected $_container = null;

    public function setSearchUtils ( $searchUtils ) {
        $this->_searchUtils = $searchUtils;
    }

    public function setContainer ( $container ) {
        $this->_container = $container;
    }

    protected function getValues ( $args ) {

        $protocol = "GET";
        $query = "/_search";
        $parameters = $this->getRequest();
        $result = $this->_searchUtils->request ($protocol, $query, $parameters);

        $output = $this->formatResult($result);

        if ($args["type"] == "timeseries") {

            return "
            x: 'x',
            columns: [
                ".$output."
            ]        ";

        } else if ($args["type"] == "html") {

            return $output;

        } else {

            return "
            columns: [
                ".$output."
            ],
            type: '".$args["type"]."',
            labels: true
        ";

        }

    }

}