<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Leads
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Leads {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $firstname
     * @ORM\Column(name="firstname", type="string", nullable=true)
     */
    protected $firstname;

    /**
     * @var string $lastname
     * @ORM\Column(name="lastname", type="string", nullable=true)
     */
    protected $lastname;

    /**
     * @var longtext $content
     * @ORM\Column (name="content", type="text", nullable=true)
     */
    protected $content;

    /**
     * @var int $status
     * @ORM\Column (name="status", type="integer", nullable=true)
     */
    protected $status;

    /**
     * @var datetime $exportdate
     * @ORM\Column (name="exportdate", type="datetime", nullable=true)
     */
    protected $exportdate;

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\datetime $exportdate
     */
    public function setExportdate($exportdate)
    {
        $this->exportdate = $exportdate;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\datetime
     */
    public function getExportdate()
    {
        return $this->exportdate;
    }

    /**
     * @param string $firstname
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
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


}
