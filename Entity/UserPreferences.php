<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;


class UserPreferences {

    protected $dataPeriodMinDateBis = null;
    protected $dataPeriodMaxDate = null;
    protected $dataZoomOption = null;
    protected $dataTypeOfGraph = null;
    protected $dataDisplayAverage = null;
    protected $dataDisplayTotal = null;

    public function __construct () {



        // date
        $datetime = new \DateTime();

        // Set default values
        $this->setDataPeriodMaxDate( $datetime );

        $yearBefore = new \DateTime();
        $yearBefore->sub( new \DateInterval( "P1M" ) );
        $this->setDataPeriodMinDate( $yearBefore );

        $this->dataZoomOption = "none";
        $this->dataTypeOfGraph = "bar";
        $this->dataDisplayTotal = true;
        $this->dataDisplayAverage = true;

    }

    /**
     * @return null
     */
    public function getDataPeriodMinDate()
    {
        return $this->dataPeriodMinDateBis;
    }

    /**
     * @param null $dataPeriodMinDate
     */
    public function setDataPeriodMinDate($dataPeriodMinDate)
    {
        $this->dataPeriodMinDateBis = $dataPeriodMinDate;
    }

    /**
     * @return null
     */
    public function getDataPeriodMaxDate()
    {
        return $this->dataPeriodMaxDate;
    }

    /**
     * @param null $dataPeriodMaxDate
     */
    public function setDataPeriodMaxDate($dataPeriodMaxDate)
    {
        $this->dataPeriodMaxDate = $dataPeriodMaxDate;
    }

    /**
     * @return null
     */
    public function getDataPeriodMinDateBis()
    {
        return $this->dataPeriodMinDateBis;
    }

    /**
     * @param null $dataPeriodMinDateBis
     */
    public function setDataPeriodMinDateBis($dataPeriodMinDateBis)
    {
        $this->dataPeriodMinDateBis = $dataPeriodMinDateBis;
    }

    /**
     * @return null
     */
    public function getDataZoomOption()
    {
        return $this->dataZoomOption;
    }

    /**
     * @param null $dataZoomOption
     */
    public function setDataZoomOption($dataZoomOption)
    {
        $this->dataZoomOption = $dataZoomOption;
    }

    /**
     * @return null
     */
    public function getDataTypeOfGraph()
    {
        return $this->dataTypeOfGraph;
    }

    /**
     * @param null $dataTypeOfGraph
     */
    public function setDataTypeOfGraph($dataTypeOfGraph)
    {
        $this->dataTypeOfGraph = $dataTypeOfGraph;
    }

    /**
     * @return null
     */
    public function getDataDisplayAverage()
    {
        return $this->dataDisplayAverage;
    }

    /**
     * @param null $dataDisplayAverage
     */
    public function setDataDisplayAverage($dataDisplayAverage)
    {
        $this->dataDisplayAverage = $dataDisplayAverage;
    }

    /**
     * @return null
     */
    public function getDataDisplayTotal()
    {
        return $this->dataDisplayTotal;
    }

    /**
     * @param null $dataDisplayTotal
     */
    public function setDataDisplayTotal($dataDisplayTotal)
    {
        $this->dataDisplayTotal = $dataDisplayTotal;
    }




}
