<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Leads
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\EntrepriseRepository")
 */
class Entreprise
{

    public function __construct()
    {
        $this->leads = new ArrayCollection();
        $this->clients = new ArrayCollection();
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
     * @ORM\Column(type="string", nullable=true, name="name")
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Leads", mappedBy="leads")
     */
    protected $leads;

    /**
     * @ORM\OneToMany(targetEntity="EntrepriseAdress", mappedBy="adresses")
     */
    protected $adresses;

    /**
     * @ORM\ManyToMany(targetEntity="Client", inversedBy="entreprises", cascade={"persist"})
     */
    protected $clients;

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
     * @return mixed
     */
    public function getClients()
    {
        return $this->clients;
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



}
