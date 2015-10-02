<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\MkgSegmentation
 * 
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\MkgSegmentationRepository")
 */
class MkgSegmentation {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(type="string", nullable=true, name="name")
     */
    protected $name;

    /**
     * @var longtext $description
     * @ORM\Column(type="text", nullable=true, name="description")
     */
    protected $description;

    /**
     * @var string $code
     * @ORM\Column(type="string", nullable=true, name="code")
     */
    protected $code;

    /**
     * @var string $nbDays
     * @ORM\Column(type="string", nullable=true, name="nbdays")
     */
    protected $nbDays;

    /**
     * @var string $utmcampaign
     * @ORM\Column(type="string", nullable=true, name="searchQuery")
     */
    protected $searchQuery;

    /**
     * @var longtext $source
     * @ORM\Column(type="text", nullable=true, name="source")
     */
	protected $fields;

	/**
	 * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Scope")
	 * @ORM\JoinColumn(name="scope", referencedColumnName="id")
	 */
	protected $scope;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cronexpression;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $emails;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastrun;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $nextrun;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $confirmationemailssubjects;

    /**
     * @var longtext $source
     * @ORM\Column(type="text", nullable=true, name="conf_email_source")
     */
    protected $confirmationEmailSource;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $log;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNbDays()
    {
        return $this->nbDays;
    }

    /**
     * @param string $nbDays
     */
    public function setNbDays($nbDays)
    {
        $this->nbDays = $nbDays;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return longtext
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param longtext $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param string $searchQuery
     */
    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }

    /**
     * @return longtext
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param longtext $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getCronexpression()
    {
        return $this->cronexpression;
    }

    /**
     * @param mixed $cronexpression
     */
    public function setCronexpression($cronexpression)
    {
        $this->cronexpression = $cronexpression;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getLastrun()
    {
        return $this->lastrun;
    }

    /**
     * @param mixed $lastrun
     */
    public function setLastrun($lastrun)
    {
        $this->lastrun = $lastrun;
    }

    /**
     * @return mixed
     */
    public function getNextrun()
    {
        return $this->nextrun;
    }

    /**
     * @param mixed $nextrun
     */
    public function setNextrun($nextrun)
    {
        $this->nextrun = $nextrun;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param mixed $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }

    /**
     * @return mixed
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param mixed $emails
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }

    /**
     * @return longtext
     */
    public function getConfirmationEmailSource()
    {
        return $this->confirmationEmailSource;
    }

    /**
     * @param longtext $confirmationEmailSource
     */
    public function setConfirmationEmailSource($confirmationEmailSource)
    {
        $this->confirmationEmailSource = $confirmationEmailSource;
    }

    /**
     * @return mixed
     */
    public function getConfirmationemailssubjects()
    {
        return $this->confirmationemailssubjects;
    }

    /**
     * @param mixed $confirmationemailssubjects
     */
    public function setConfirmationemailssubjects($confirmationemailssubjects)
    {
        $this->confirmationemailssubjects = $confirmationemailssubjects;
    }

}
