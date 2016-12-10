<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * LeadsFactoryBundle\Entity\ReferenceListElement
 *
 * @ORM\Entity(repositoryClass="LeadsFactoryBundle\Entity\ReferenceListElementRepository")
 */
class ReferenceListElement
{

    public static $_STATUS_ENABLED = 1;
    public static $_STATUS_DISABLED = 1;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $parent_id;

    /**
     * @ORM\Column(type="integer", nullable=true,  name="referencelist_id")
     */
    protected $referencelist_id;

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
     * @ORM\OneToMany(targetEntity="LeadsFactoryBundle\Entity\ReferenceListElement", mappedBy="parent")
     */
    protected $children;

    /**
     * @var string $parent
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\ReferenceListElement", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\ReferenceList", inversedBy="elements")
     * @ORM\JoinColumn(name="referencelist_id", referencedColumnName="id")
     */
    protected $referenceList;

    /**
     * @ORM\Column(type="integer", nullable=true,  name="rank", options={"default":1})
     */
    protected $rank;

    /**
     * @ORM\Column(type="integer", nullable=true,  name="status")
     */
    protected $status;


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
     * @param \LeadsFactoryBundle\Entity\longtext $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return \LeadsFactoryBundle\Entity\longtext
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
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
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
     * Add children
     *
     * @param \LeadsFactoryBundle\Entity\ReferenceListElement $children
     * @return ReferenceListElement
     */
    public function addChild(\LeadsFactoryBundle\Entity\ReferenceListElement $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \LeadsFactoryBundle\Entity\ReferenceListElement $children
     */
    public function removeChild(\LeadsFactoryBundle\Entity\ReferenceListElement $children)
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


    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param mixed $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return mixed
     */
    public function getReferenceListId()
    {
        return $this->referencelist_id;
    }

    /**
     * @param mixed $referenceList_id
     */
    public function setReferenceListId($referenceList_id)
    {
        $this->referencelist_id = $referenceList_id;
    }

}
