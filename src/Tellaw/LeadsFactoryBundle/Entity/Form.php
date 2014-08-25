<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Form
 *
 * 
 * @ORM\Entity
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
     * @var longtext $style
     * @ORM\Column(type="text", nullable=true, name="style")
     */
    protected $style;

    /**
     * @var longtext $exportConfig
     * @ORM\Column(type="text", nullable=true, name="export_config")
     */
    protected $exportConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\FormType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $formType;

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
     * @param mixed $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
    }

    /**
     * @return mixed
     */
    public function getStyle()
    {
        return $this->style;
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
    public function getExportMethods()
    {
        return json_decode(trim($this->getExportConfig()), true);
    }

}
