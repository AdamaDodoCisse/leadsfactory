<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *
 * LeadsFactoryBundle\Entity\LeadsSandbox
 *
 * @ORM\Entity(repositoryClass="LeadsFactoryBundle\Entity\LeadsSandboxRepository")
 */
class LeadsSandbox
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
     * @var string uniqId
     * @ORM\Column(type="string", nullable=true, name="uniqId")
     */
    protected $uniqId;

    /**
     * @var string formCode
     * @ORM\Column(type="string", nullable=true, name="formCode")
     */
    protected $formCode;

    /**
     * @ORM\Column(type="text", nullable=true, name="content")
     */
    private $data;

    /**
     * @var int $status
     * @ORM\Column(type="integer", nullable=true, name="status")
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ipadress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $userAgent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $delay;

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
     * @return string
     */
    public function getUniqId()
    {
        return $this->uniqId;
    }

    /**
     * @param string $uniqId
     */
    public function setUniqId($uniqId)
    {
        $this->uniqId = $uniqId;
    }

    /**
     * @return string
     */
    public function getFormCode()
    {
        return $this->formCode;
    }

    /**
     * @param string $formCode
     */
    public function setFormCode($formCode)
    {
        $this->formCode = $formCode;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getIpadress()
    {
        return $this->ipadress;
    }

    /**
     * @param mixed $ipadress
     */
    public function setIpadress($ipadress)
    {
        $this->ipadress = $ipadress;
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param mixed $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return mixed
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param mixed $delay
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    /**
     * @return mixed
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param mixed $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }



}
