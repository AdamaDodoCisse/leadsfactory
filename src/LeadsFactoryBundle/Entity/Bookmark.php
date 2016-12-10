<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="LeadsFactoryBundle\Entity\BookmarkRepository")
 * @ORM\Table(name="bookmark")
 */
class Bookmark
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
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\Users")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
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
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\Form")
     * @ORM\JoinColumn(name="form", referencedColumnName="id")
     */
    protected $form;

    /**
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\FormType")
     * @ORM\JoinColumn(name="formType", referencedColumnName="id")
     */
    protected $formType;

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
     * @param \LeadsFactoryBundle\Entity\User $users
     * @return Bookmark
     */
    public function setUser(\LeadsFactoryBundle\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \LeadsFactoryBundle\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set form
     *
     * @param \LeadsFactoryBundle\Entity\Form $form
     * @return Bookmark
     */
    public function setForm(\LeadsFactoryBundle\Entity\Form $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return \LeadsFactoryBundle\Entity\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set formType
     *
     * @param \LeadsFactoryBundle\Entity\FormType $formType
     * @return Bookmark
     */
    public function setFormType(\LeadsFactoryBundle\Entity\FormType $formType = null)
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * Get formType
     *
     * @return \LeadsFactoryBundle\Entity\FormType
     */
    public function getFormType()
    {
        return $this->formType;
    }
}
