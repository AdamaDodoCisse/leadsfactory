<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Tracking
 * 
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\TrackingRepository")
 */
class Tracking {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $name
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Form")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    protected $form;

    /**
     * @var longtext $utm_campaign
     * @ORM\Column(type="string", nullable=true, name="utm_campaign")
     */
    protected $utm_campaign;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     */
    protected $created_at;


    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param string $form
     */
    public function setForm($form)
    {
        $this->form = $form;
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
     * @return longtext
     */
    public function getUtmCampaign()
    {
        return $this->utm_campaign;
    }

    /**
     * @param longtext $utm_campaign
     */
    public function setUtmCampaign($utm_campaign)
    {
        $this->utm_campaign = $utm_campaign;
    }

}
