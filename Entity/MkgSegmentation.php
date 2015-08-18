<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\MkgSegmentation
 * 
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\MkgSegmentationRepository")
 */
class MkgSegmentation {

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
    protected $searchQuery;

    /**
     * @var longtext $source
     * @ORM\Column(type="text", nullable=true, name="source")
     */
	protected $fields;

	/**
	 * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Scope")
	 * @ORM\JoinColumn(name="scope", referencedColumnName="id")
	 */
	protected $scope;

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
     * @return longtext
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param longtext $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param string $searchQuery
     */
    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }

    /**
     * @return longtext
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param longtext $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }



}
