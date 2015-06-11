<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\User
 * 
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\UserRepository")
 */
class Preference {

    public static $_SCOPE_USER = 1;
    public static $_SCOPE_APPLICATION = 2;

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var longtext $key
     * @ORM\Column(type="string", nullable=true)
     */
    protected $key;

    /**
     * @var longtext value
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $scope;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     */
    protected $created_at;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Users")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param longtext $key
     */
    public function setKey($key)
    {
        $this->key = $key;
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
