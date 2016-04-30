<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * Entreprise
 *
 * This entity class describe the company of a person.
 *
 * * An entreprise can handle many persons
 * * A Person can be linked to multiple entreprise.
 *
 * The link between entreprise and person also handle position information.
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\EntrepriseRepository")
 */
class Entreprise
{

    public function __construct()
    {
        $this->leads = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->scopes = new ArrayCollection();
    }

	/**
	 * Unique ID of an Entreprise
     *
     * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var String Name of the entreprise*
     * @ORM\Column(type="string", nullable=true, name="name")
     */
    protected $name;

    /**
     * @var String Siret of the Entreprise
     * @ORM\Column(type="string", nullable=true, name="siret")
     */
    protected $siret;

    /**
     * @var String Phone number of the entreprise
     * @ORM\Column(type="string", nullable=true, name="phone")
     */
    protected $phone;

    /**
     * @var Leads Leads affected to this entreprise
     * @ORM\OneToMany(targetEntity="Leads", mappedBy="leads")
     */
    protected $leads;

    /**
     * @var EntrepriseAdress Adresses of the entreprise.
     * @ORM\OneToMany(targetEntity="EntrepriseAdress", mappedBy="adresses")
     */
    protected $adresses;


    /**
     * @var Scope scope of the Entreprise
     * @ORM\ManyToMany(targetEntity="Scope")
     * @ORM\JoinTable(name="entreprise_scope",
     *      joinColumns={@ORM\JoinColumn(name="entreprise_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="scope_id", referencedColumnName="id")}
     *      )
     */
    private $scopes;

    /**
     * @return mixed
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param mixed $scopes
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @ORM\Column(type="string", nullable=true, name="uid")
     */
    protected $uid;

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getAdresses()
    {
        return $this->adresses;
    }

    /**
     * @param mixed $clients
     */
    public function setClients($clients)
    {
        $this->clients = $clients;
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

    /**
     * @return mixed
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param mixed $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;
    }

    /**
     * @return mixed
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * @param mixed $siret
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;
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



}
