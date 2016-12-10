<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 *
 * @ORM\Entity(repositoryClass="LeadsFactoryBundle\Entity\DataDictionnaryElementRepository")
 */
class DataDictionnaryElement
{

    public static $_STATUS_ENABLED = 1;
    public static $_STATUS_DISABLED = 0;

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
     * @ORM\Column(type="integer", nullable=true,  name="dataDictionnary_id")
     */
    protected $dataDictionnary_id;

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
     * @ORM\OneToMany(targetEntity="LeadsFactoryBundle\Entity\DataDictionnaryElement", mappedBy="parent")
     */
    protected $children;

    /**
     * @var string $parent
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\DataDictionnaryElement", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\DataDictionnary", inversedBy="elements")
     * @ORM\JoinColumn(name="dataDictionnary_id", referencedColumnName="id")
     */
    protected $dataDictionnary;

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
    public function setDataDictionnary($dataDictionnary)
    {
        $this->dataDictionnary = $dataDictionnary;
    }

    /**
     * @return mixed
     */
    public function getDataDictionnary()
    {
        return $this->dataDictionnary;
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
    public function addChild(\LeadsFactoryBundle\Entity\DataDictionnaryElement $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \LeadsFactoryBundle\Entity\ReferenceListElement $children
     */
    public function removeChild(\LeadsFactoryBundle\Entity\DataDictionnaryElement $children)
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
