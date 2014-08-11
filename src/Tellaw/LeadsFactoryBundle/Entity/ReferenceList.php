<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\ReferenceList
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ReferenceList {

    /**
     * @ORM\OneToMany(targetEntity="ReferenceListElement", mappedBy="referenceList")
     */
    protected $elements;

    public function __construct()
    {
        $this->forms = new ArrayCollection();
    }

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $code
     * @ORM\Column(name="code", type="string", nullable=true)
     */
    protected $code;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected $name;

    /**
     * @var longtext $description
     * @ORM\Column (name="description", type="text", nullable=true)
     */
    protected $description;


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
    public function getCode()
    {
        return $this->code;
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
     * @param mixed $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return mixed
     */
    public function getElements()
    {
        return $this->elements;
    }


}
