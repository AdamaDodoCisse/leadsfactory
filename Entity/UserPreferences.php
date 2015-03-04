<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;


class UserPreferences {

    protected $dataPeriodMinDateBis = null;
    protected $dataPeriodMaxDate = null;

    public function __construct () {


/*
        // date
        $datetime = new \DateTime();

        // Set default values
        $this->setDataPeriodMaxDate( $datetime );

        $yearBefore = new \DateTime();
        $yearBefore->sub( new \DateInterval( "P1A" ) );
        $this->setDataPeriodMinDate( $yearBefore );
*/
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




}
