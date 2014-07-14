<?php

namespace Tellaw\MonitorUBundle\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class User implements UserInterface {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	protected $username;
	
	/**
	 * @var password $password
	 * @ORM\Column(type="string", length=50)
	 */
	protected $password;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $firstname;
	
	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $lastname;
	
    /**
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="users")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;
    
	/**
	 * @var integer $accountId
	 *
	 * @ORM\Column(name="account_id", type="integer")
	 */
	private $accountId;
	
	public function getRoles() {
		return array('ROLE_ADMIN');
	}
	public function setPassword ( $password ) {
		$this->password = $password;
	}
	public function getPassword() {
		return $this->password;

	}
	public function getSalt() {
		return null;

	}
	public function setUsername ($username) {
		$this->username = $username;
	}
	public function getUsername() {
		return $this->username;

	}
	public function eraseCredentials() {
		return null;
	}
	public function equals(UserInterface $user) {
		
		if ($user->getUsername() == $this->getUsername())
			return true;
		else
			return false;

	}

	public function getId() {
		return $this->id;
	}
	public function setId( $id ) {
		$this->id = $id;
	}
	
	public function getFirstname()
	{
	    return $this->firstname;
	}

	public function setFirstname($firstname)
	{
	    $this->firstname = $firstname;
	}

	public function getLastname()
	{
	    return $this->lastname;
	}

	public function setLastname($lastname)
	{
	    $this->lastname = $lastname;
	}

	public function getAccount()
	{
	    return $this->account;
	}

	public function setAccount($account)
	{
	    $this->account = $account;
	}

	public function getAccountId()
	{
	    return $this->accountId;
	}

	public function setAccountId($accountId)
	{
	    $this->accountId = $accountId;
	}
}
