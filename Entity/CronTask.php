<?php
namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
* @ORM\Entity
* @UniqueEntity("name")
* @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\CronTaskRepository")
*/
class CronTask
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;

    /**
    * @ORM\Column(type="string")
    */
    private $name;

    /**
    * @ORM\Column(type="string")
    */
    private $commands;

    /**
     * @ORM\Column(type="string")
     */
    private $cronexpression;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
    * @ORM\Column(type="datetime", nullable=true)
    */
    private $lastrun;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $nextrun;

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
    private $serviceName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $log;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getCommands()
    {
        return explode("|",$this->commands);
    }

    public function setCommands($commands)
    {
        $this->commands = implode("|",$commands);
        return $this;
    }

    public function getCommandsAsString()
    {
        return $this->commands;
    }

    public function setCommandsAsString($commands)
    {
        $this->commands = $commands;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCronexpression()
    {
        return $this->cronexpression;
    }

    /**
     * @param mixed $cronexpression
     */
    public function setCronexpression($cronexpression)
    {
        $this->cronexpression = $cronexpression;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getLastRun()
    {
        return $this->lastrun;
    }

    public function setLastRun($lastrun)
    {
        $this->lastrun = $lastrun;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNextrun()
    {
        return $this->nextrun;
    }

    /**
     * @param mixed $nextrun
     */
    public function setNextrun($nextrun)
    {
        $this->nextrun = $nextrun;
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

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param mixed $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param mixed $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

}