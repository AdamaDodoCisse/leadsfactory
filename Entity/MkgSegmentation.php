<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\MkgSegmentation
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\MkgSegmentationRepository")
 */
class MkgSegmentation
{

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true, name="name")
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true, name="description")
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true, name="config")
     */
    protected $config;

    /**
     * Code of the KIBANA query
     * @ORM\Column(type="string", nullable=true, name="query_code")
     */
    protected $query_code;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Scope")
     * @ORM\JoinColumn(name="scope", referencedColumnName="id")
     */
    protected $scope;


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

    /**
     * @return mixed
     */
    public function getQueryCode()
    {
        return $this->query_code;
    }

    /**
     * @param mixed $query_code
     */
    public function setQueryCode($query_code)
    {
        $this->query_code = $query_code;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


}
