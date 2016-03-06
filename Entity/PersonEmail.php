<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Leads
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\PersonEmailRepository")
 */
class PersonEmail
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
     * @ORM\Column(type="string", nullable=true, name="email")
     */
    protected $email;

    /**
     * @ORM\Column(type="integer", nullable=true, name="type")
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="validation")
     */
    protected $validation;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Person", inversedBy="person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @param mixed $validation
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;
    }

    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param mixed $client
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }



}
