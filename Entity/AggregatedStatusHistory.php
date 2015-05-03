<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\AggregatedStatusHistory
 *
 */
class AggregatedStatusHistory {

    private $statusHistoryElements = array();

    /**
     * @return array
     */
    public function getStatusHistoryElements()
    {
        return $this->statusHistoryElements;
    }

    /**
     * @param array $statusHistoryElements
     */
    public function setStatusHistoryElements($statusHistoryElements)
    {
        $this->statusHistoryElements = $statusHistoryElements;
    }

    public function addStatusHistoryElement ( StatusHistory $statusHistory ) {

        $this->statusHistoryElements[] = $statusHistory;

    }

}