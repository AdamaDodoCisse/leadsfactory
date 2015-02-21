<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Leads
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\ClientRepository")
 */
class Client
{

    public function __construct()
    {
        $this->leads = new ArrayCollection();
        $this->adresses = new ArrayCollection();
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
     * @ORM\Column(type="string", nullable=true, name="firstname")
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", nullable=true, name="lastname")
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", nullable=true, name="phone")
     */
    protected $phone;

    /**
     * @ORM\OneToMany(targetEntity="Leads", mappedBy="leads")
     */
    protected $leads;

    /**
     * @ORM\OneToMany(targetEntity="ClientAdress", mappedBy="adresses")
     */
    protected $adresses;

    /**
     * @ORM\OneToMany(targetEntity="ClientEmail", mappedBy="emails")
     */
    protected $emails;

    /**
     * @return mixed
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param mixed $emails
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }

    /**
     * @return mixed
     */
    public function getAdresses()
    {
        return $this->adresses;
    }

    /**
     * @param mixed $adresses
     */
    public function setAdresses($adresses)
    {
        $this->adresses = $adresses;
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
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * @param mixed $leads
     */
    public function setLeads($leads)
    {
        $this->leads = $leads;
    }



}
