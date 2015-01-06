<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Form
 * 
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\FormRepository")
 */
class Form {

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
     * @var string $utmcampaign
     * @ORM\Column(type="string", nullable=true, name="utmcampaign")
     */
    protected $utmcampaign;

    /**
     * @var longtext $source
     * @ORM\Column(type="text", nullable=true, name="source")
     */
    protected $source;

    /**
     * @var longtext $script
     * @ORM\Column(type="text", nullable=true, name="script")
     */
    protected $script;

    /**
     * @var longtext $exportConfig
     * @ORM\Column(type="text", nullable=true, name="export_config")
     */
    protected $exportConfig;

    /**
     * @var longtext $alertRules
     * @ORM\Column(type="text", nullable=true, name="alert_rules")
     */
    protected $alertRules;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\FormType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $formType;

    /**
     * @var longtext $source
     * @ORM\Column(type="text", nullable=true, name="conf_email_source")
     */
    protected $confirmationEmailSource;

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
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getDescription()
    {
        return $this->description;
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $script
     */
    public function setScript($script)
    {
        $this->script = $script;
    }

    /**
     * @return mixed
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set exportConfig
     *
     * @todo sanitize input data
     *
     * @param string $exportConfig
     * @return Form
     */
    public function setExportConfig($exportConfig)
    {
        $this->exportConfig = $exportConfig;

        return $this;
    }

    /**
     * Get exportConfig
     *
     * @return string 
     */
    public function getExportConfig()
    {
        return $this->exportConfig;
    }

    /**
     * Get config export methods
     *
     * @return string
     */
    public function getConfig()
    {
        return json_decode(trim($this->getExportConfig()), true);
    }

    public function getRules () {
        $alertRules = json_decode(trim($this->getAlertRules()), true);
        return $alertRules;
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $alertRules
     */
    public function setAlertRules($alertRules)
    {
        $this->alertRules = $alertRules;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getAlertRules()
    {
        return $this->alertRules;
    }

    /**
     * @return string
     */
    public function getUtmcampaign()
    {
        return $this->utmcampaign;
    }

    /**
     * @param string $utmcampaign
     */
    public function setUtmcampaign($utmcampaign)
    {
        $this->utmcampaign = $utmcampaign;
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
     * Set confirmationEmailSource
     *
     * @param string $confirmationEmailSource
     * @return Form
     */
    public function setConfirmationEmailSource($confirmationEmailSource)
    {
        $this->confirmationEmailSource = $confirmationEmailSource;

        return $this;
    }

    /**
     * Get confirmationEmailSource
     *
     * @return string 
     */
    public function getConfirmationEmailSource()
    {
        return $this->confirmationEmailSource;
    }
}
