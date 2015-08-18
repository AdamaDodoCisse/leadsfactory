<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Users
 *
 * 
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\UsersRepository")
 */
class Users implements UserInterface {

    public static $_ROLES = array (
        "ROLE_ADMIN" => "Administrateur",
        "ROLE_DEV" => "Developpeur",
        "ROLE_REPORTING" => "Responsable Reporting"
    );

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
    protected $firstname;

    /**
     * @var string $lastname
     * @ORM\Column(type="string", nullable=true, name="lastname")
     */
    protected $lastname;


    /**
     * @var string $login
     * @ORM\Column(type="string", nullable=true, name="login")
     */
    protected $login;

    /**
     * @var string $password
     * @ORM\Column(type="string", nullable=true, name="password")
     */
    protected $password;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Scope")
     * @ORM\JoinColumn(name="scope", referencedColumnName="id")
     */
    protected $scope;

    /**
     * @var string $role
     * @ORM\Column(type="string", nullable=true, name="role")
     */
    protected $role;

    /**
     * @var string $email
     * @ORM\Column(type="string", nullable=true, name="email")
     */
    protected $email;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $fistname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
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

    public function getRoles() {
        return array('ROLE_ADMIN');
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }



    public function getSalt() {
        return null;
    }

    public function eraseCredentials() {
        return null;
    }

    public function getUsername() {
        return $this->getLogin();

    }

    public function equals(UserInterface $user) {

        if ($user->getLogin() == $this->getLogin())
            return true;
        else
            return false;

    }

    /**
     * Set scope
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Scope $scope
     * @return Users
     */
    public function setScope(\Tellaw\LeadsFactoryBundle\Entity\Scope $scope = null)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return \Tellaw\LeadsFactoryBundle\Entity\Scope 
     */
    public function getScope()
    {
        return $this->scope;
    }

}
