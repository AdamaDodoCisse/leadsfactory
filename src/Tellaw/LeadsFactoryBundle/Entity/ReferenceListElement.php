<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement
 *
 * 
 * @ORM\Entity
 */
class ReferenceListElement {

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
     * @var string $value
     * @ORM\Column(type="text", nullable=true, name="value")
     */
    protected $value;

    /**
     * @var string $children
     * @ORM\OneToMany(targetEntity="Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement", mappedBy="parent")
     */
    protected $children;

    /**
     * @var string $parent
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\ReferenceList", inversedBy="elements")
     * @ORM\JoinColumn(name="referencelist_id", referencedColumnName="id")
     */
    protected $referenceList;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

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

    /**
     * Set parent
     *
     * @param string $parent
     * @return ReferenceListElement
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return string 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $children
     * @return ReferenceListElement
     */
    public function addChild(\Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $children
     */
    public function removeChild(\Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }
}
