<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * LeadsFactoryBundle\Entity\FormType
 *
 *
 * @ORM\Entity(repositoryClass="LeadsFactoryBundle\Entity\FormTypeRepository")
 */
class FormType
{

    public $type = "formType";

    /**
     * @ORM\OneToMany(targetEntity="LeadsFactoryBundle\Entity\Form", mappedBy="formType")
     */
    protected $forms;

    public function __construct()
    {
        $this->forms = new ArrayCollection();
        $this->leads = new ArrayCollection();
    }

    public function getType()
    {
        return $this->type;
    }

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
     * @ORM\OneToMany(targetEntity="LeadsFactoryBundle\Entity\Leads", mappedBy="formType")
     */
    private $leads;

    /**
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\Scope")
     * @ORM\JoinColumn(name="scope", referencedColumnName="id")
     */
    protected $scope;

    /**
     * @var longtext $alertRules
     * @ORM\Column(type="text", nullable=true, name="alert_rules")
     */
    protected $alertRules;

    /**
     * @param \LeadsFactoryBundle\Entity\longtext $alertRules
     */
    public function setAlertRules($alertRules)
    {
        $this->alertRules = $alertRules;
    }

    /**
     * @return \LeadsFactoryBundle\Entity\longtext
     */
    public function getAlertRules()
    {
        return $this->alertRules;
    }

    /**
     * @param mixed $leads
     */
    public function setLeads($leads)
    {
        $this->leads = $leads;
    }

    /**
     * @return mixed
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * @param \LeadsFactoryBundle\Entity\longtext $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \LeadsFactoryBundle\Entity\longtext
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
     * @param mixed $forms
     */
    public function setForms($forms)
    {
        $this->forms = $forms;
    }

    /**
     * @return mixed
     */
    public function getForms()
    {
        return $this->forms;
    }

    public function __toString()
    {
        return $this->name;
    }


    /**
     * Add leads
     *
     * @param \LeadsFactoryBundle\Entity\Leads $leads
     * @return FormType
     */
    public function addLead(\LeadsFactoryBundle\Entity\Leads $leads)
    {
        $this->leads[] = $leads;

        return $this;
    }

    /**
     * Remove leads
     *
     * @param \LeadsFactoryBundle\Entity\Leads $leads
     */
    public function removeLead(\LeadsFactoryBundle\Entity\Leads $leads)
    {
        $this->leads->removeElement($leads);
    }

    /**
     * Set scope
     *
     * @param \LeadsFactoryBundle\Entity\Scope $scope
     * @return FormType
     */
    public function setScope(\LeadsFactoryBundle\Entity\Scope $scope = null)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return \LeadsFactoryBundle\Entity\Scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    public function getRules()
    {
        $alertRules = json_decode(trim($this->getAlertRules()), true);

        return $alertRules;
    }

    public $todayValue = null;
    public $yesterdayValue = null;
    public $weekBeforeValue = null;
    public $yesterdayStatusColor = null;
    public $yesterdayStatusText = null;
    public $yesterdayVariation = null;

    public $textualYesterdayDay = null;
    public $textualWeekBeforeDay = null;

    public $yesterdayStatus = null;

    public $nbViews = null;
    public $nbLeads = null;
    public $transformRate = null;
}
