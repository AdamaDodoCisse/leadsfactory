<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="export_history")
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
     * @ORM\Column(type="datetime", nullable=true, name="exportdate")
     */
    protected $exportdate;

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
     * Set exportdate
     *
     * @param \DateTime $exportdate
     * @return Export
     */
    public function setExportdate($exportdate)
    {
        $this->exportdate = $exportdate;

        return $this;
    }

    /**
     * Get exportdate
     *
     * @return \DateTime 
     */
    public function getExportdate()
    {
        return $this->exportdate;
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
}
