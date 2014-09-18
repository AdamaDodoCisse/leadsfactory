<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="export_jobs")
 */
class Export {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Leads")
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id")
     */
    protected $lead;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Form")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    protected $form;

    /**
     * @ORM\Column(type="string", nullable=false, name="method")
     */
    protected $method;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="scheduled_at")
     */
    protected $scheduled_at;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="executed_at")
     */
    protected $executed_at;

    /**
     * @var int $status
     * @ORM\Column(type="integer", nullable=true, name="status")
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $log;

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
     * Set status
     *
     * @param integer $status
     * @return Export
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set lead
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Leads $lead
     * @return Export
     */
    public function setLead(\Tellaw\LeadsFactoryBundle\Entity\Leads $lead = null)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * Get lead
     *
     * @return \Tellaw\LeadsFactoryBundle\Entity\Leads 
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * Set form
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @return Export
     */
    public function setForm(\Tellaw\LeadsFactoryBundle\Entity\Form $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return \Tellaw\LeadsFactoryBundle\Entity\Form 
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set log
     *
     * @param string $log
     * @return Export
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return string 
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Export
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set scheduled_at
     *
     * @param \DateTime $scheduledAt
     * @return Export
     */
    public function setScheduledAt($scheduledAt)
    {
        $this->scheduled_at = $scheduledAt;

        return $this;
    }

    /**
     * Get scheduled_at
     *
     * @return \DateTime 
     */
    public function getScheduledAt()
    {
        return $this->scheduled_at;
    }

    /**
     * Set executed_at
     *
     * @param \DateTime $executedAt
     * @return Export
     */
    public function setExecutedAt($executedAt)
    {
        $this->executed_at = $executedAt;

        return $this;
    }

    /**
     * Get executed_at
     *
     * @return \DateTime 
     */
    public function getExecutedAt()
    {
        return $this->executed_at;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return Export
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }
}
