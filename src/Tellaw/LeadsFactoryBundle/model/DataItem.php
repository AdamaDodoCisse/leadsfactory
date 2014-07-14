<?php

namespace Tellaw\MonitorUBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tellaw\MonitorUBundle\Entity\DataItem
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class DataItem
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $entrytime
     *
     * @ORM\Column(name="entrytime", type="datetime")
     */    
    private $entrytime;

    
    /**
     * @ORM\ManyToOne(targetEntity="Monitor", inversedBy="dataitems")
     * @ORM\JoinColumn(name="monitor_id", referencedColumnName="id")
     */
    private $monitor;

    
    /**
     * @ORM\Column(name="monitor_id", type="integer", nullable="false")
     */
    private $monitor_id;
    
    /**
     * @var string $monitorvalue
     *
     * @ORM\Column(name="monitorvalue", type="integer")
     */
    private $monitorvalue;

    /**
     * @var string $monitordate
     *
     * @ORM\Column(name="monitordate", type="date")
     */
    private $monitordate;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

	public function getEntryTime () {
		return $this->entrytime;
	}
	
	public function setEntryTime ( $entrytime ) {
		$this->entrytime = $entrytime;
	}
	
	public function getMonitorDate () {
		return $this->monitordate;
	}
	
	public function setMonitorDate ( $monitorDate ) {
		$this->monitordate = $monitorDate;
	}
	
	public function getMonitorId () {
		return $this->monitor_id;
	}
	
	public function setMonitorId ( $monitorId ) {
		$this->monitor_id = $monitorId;
	}
	
	public function getMonitorValue () {
		return $this->monitorvalue;
	}
	
	public function setMonitor ( $monitor ) {
		$this->monitor = $monitor;
	}
	public function getMonitor () {
		return $this->monitor;
	}
	
	public function setMonitorValue ( $monitorvalue ) {
		$this->monitorvalue = $monitorvalue;
	}
	
}