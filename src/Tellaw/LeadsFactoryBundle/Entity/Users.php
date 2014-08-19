<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Users
 *
 * 
 * @ORM\Entity
 */
class Users {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $firstname
     * @ORM\Column(type="string", nullable=true, name="firstname")
     */
    protected $fistname;

    /**
     * @var string $lastname
     * @ORM\Column(type="text", nullable=true, name="lastname")
     */
    protected $lastname;


    /**
     * @var string $login
     * @ORM\Column(type="text", nullable=true, name="login")
     */
    protected $login;

    /**
     * @var string $password
     * @ORM\Column(type="text", nullable=true, name="password")
     */
    protected $password;

    /**
     * @param string $fistname
     */
    public function setFistname($fistname)
    {
        $this->fistname = $fistname;
    }

    /**
     * @return string
     */
    public function getFistname()
    {
        return $this->fistname;
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
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getPassword()
    {
        return $this->password;
    }



}
