<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 07/12/15
 * Time: 12:08
 */

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\MkgSegmentation
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\MkgSegmentationRepository")
 */
class MkgSegment
{

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
     * @var string $filter
     * @ORM\Column(type="text", name="filter", nullable=true)
     */
    protected $filter;

    /**
     * @var string
     */
    protected $filter_txt;

    /**
     * @ORM\Column(type="integer", name="segmentation_id", nullable=false)
     */
    protected $segmentation;

    /**
     * @ORM\Column(type="integer", name="nb_days", nullable=true)
     */
    protected $nbDays;

    /**
     * @ORM\Column(type="date", name="date_start", nullable=false)
     */
    protected $dateStart;

    /**
     * @ORM\Column(type="date", name="date_end", nullable=false)
     */
    protected $dateEnd;

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
     * @var datetime $created
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $updated;


    public function __construct()
    {
        $this->created= new \DateTime("now");
        $this->updated= new \DateTime("now");
    }

    /**
     * Gets triggered only on insert

     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime("now");
    }

    /**
     * Gets triggered every time on update

     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime("now");
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param string $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
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
     * @return mixed
     */
    public function getFilterTxt()
    {
        return $this->filter_txt;
    }

    /**
     * @param mixed $filter_txt
     */
    public function setFilterTxt($filter_txt)
    {
        $this->filter_txt = $filter_txt;
    }

    /**
     * @return mixed
     */
    public function getSegmentation()
    {
        return $this->segmentation;
    }

    /**
     * @param mixed $segmentation
     */
    public function setSegmentation($segmentation)
    {
        $this->segmentation = $segmentation;
    }

    /**
     * @return mixed
     */
    public function getNbDays()
    {
        return $this->nbDays;
    }

    /**
     * @param mixed $nbDays
     */
    public function setNbDays($nbDays)
    {
        $this->nbDays = $nbDays;
    }

    /**
     * @return mixed
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
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
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param datetime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }


}