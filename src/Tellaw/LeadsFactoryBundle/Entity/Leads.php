<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Leads
 *
 * 
 * @ORM\Entity
 */
class Leads {

    public static $_EXPORT_NOT_PROCESSED = 0;
    public static $_EXPORT_SUCCESS = 1;
    public static $_EXPORT_ONE_TRY_ERROR = 2;
    public static $_EXPORT_MULTIPLE_ERROR = 3;

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $firstname
     * @ORM\Column(type="string", nullable=true, name="firstname")
     */
    protected $firstname;

    /**
     * @var string $lastname
     * @ORM\Column(type="string", nullable=true, name="lastname")
     */
    protected $lastname;

    /**
     * @ORM\Column(type="text", nullable=true, name="content")
     */
    private $data;

    /**
     * @var longtext $content
     * 
     */
    protected $content;

    /**
     * @var int $status
     * @ORM\Column(type="integer", nullable=true, name="status")
     */
    protected $status;

    /**
     * @var datetime $exportdate
     * @ORM\Column(type="datetime", nullable=true, name="exportdate")
     */
    protected $exportdate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $log;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\FormType", inversedBy="leads")
     * @ORM\JoinColumn(name="form_type_id", referencedColumnName="id")
     */
    private $formType;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Form")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    private $form;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $utmcampaign;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;


    /**
     * @param mixed $formType
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;
    }

    /**
     * @return mixed
     */
    public function getFormType()
    {
        return $this->formType;
    }
    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\datetime $exportdate
     */
    public function setExportdate($exportdate)
    {
        $this->exportdate = $exportdate;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\datetime
     */
    public function getExportdate()
    {
        return $this->exportdate;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param mixed $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param mixed $utmcampaign
     */
    public function setUtmcampaign($utmcampaign)
    {
        $this->utmcampaign = $utmcampaign;
    }

    /**
     * @return mixed
     */
    public function getUtmcampaign()
    {
        return $this->utmcampaign;
    }

    public function getExportConfig()
    {

    }


    /**
     * Set formId
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $formId
     * @return Leads
     */
    public function setFormId(\Tellaw\LeadsFactoryBundle\Entity\Form $formId = null)
    {
        $this->formId = $formId;

        return $this;
    }

    /**
     * Get formId
     *
     * @return \Tellaw\LeadsFactoryBundle\Entity\Form 
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Set form
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @return Leads
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
     * Return new export error status
     *
     * @param $lead
     * @return mixed
     */
    public function getNewErrorStatus(){
        if($this->getStatus == self::$_EXPORT_NOT_PROCESSED || is_null($this->getStatus())){
            return self::$_EXPORT_MULTIPLE_ERROR;
        }else{
            return self::$_EXPORT_ONE_TRY_ERROR;
        }

    }
}
