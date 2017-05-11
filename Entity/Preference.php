<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Preference
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\PreferenceRepository")
 */
class Preference
{
    /**
     * @return longtext
     */
    public function getKeyval()
    {
        return $this->keyval;
    }

    /**
     * @param longtext $keyval
     */
    public function setKeyval($keyval)
    {
        $this->keyval = $keyval;
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

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var longtext $keyval
     * @ORM\Column(type="string", nullable=true)
     */
    protected $keyval;

    /**
     * @var longtext value
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Scope")
     * @ORM\JoinColumn(name="scope", referencedColumnName="id")
     */
    protected $scope;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     */
    protected $created_at;

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
     * @return longtext
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param longtext $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }


}
