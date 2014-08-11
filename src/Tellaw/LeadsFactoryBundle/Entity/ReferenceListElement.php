<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ReferenceListElement {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected $name;

    /**
     * @var longtext $description
     * @ORM\Column (name="description", type="text", nullable=true)
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="ReferenceList", inversedBy="elements")
     * @ORM\JoinColumn(name="referencelist_id", referencedColumnName="id")
     */
    protected $referenceList;

    /**
     * @param mixed $referenceList
     */
    public function setReferenceList($referenceList)
    {
        $this->referenceList = $referenceList;
    }

    /**
     * @return mixed
     */
    public function getReferenceList()
    {
        return $this->referenceList;
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getValue()
    {
        return $this->value;
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


}
