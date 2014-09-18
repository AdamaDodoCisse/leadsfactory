<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="bookmark")
 */
class Bookmark {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Users")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string $entity
     * @ORM\Column(type="string", nullable=false, name="entity_name")
     */
    protected $entity_name;


    /**
     * @var $entity
     * @ORM\Column(type="integer", nullable=false, name="entity_id")
     */
    protected $entity_id;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entity_name
     *
     * @param string $entityName
     * @return Bookmark
     */
    public function setEntityName($entityName)
    {
        $this->entity_name = $entityName;

        return $this;
    }

    /**
     * Get entity_name
     *
     * @return string 
     */
    public function getEntityName()
    {
        return $this->entity_name;
    }

    /**
     * Set entity_id
     *
     * @param int $entityId
     * @return Bookmark
     */
    public function setEntityId($entityId)
    {
        $this->entity_id = $entityId;

        return $this;
    }

    /**
     * Get entity_id
     *
     * @return \integer
     */
    public function getEntityId()
    {
        return $this->entity_id;
    }

    /**
     * Set user
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\User $user
     * @return Bookmark
     */
    public function setUser(\Tellaw\LeadsFactoryBundle\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Tellaw\LeadsFactoryBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
