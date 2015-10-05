<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Leads
 *
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\LeadsRepository")
 */
class Leads
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
     * @var string $firstname
     * @ORM\Column(type="string", nullable=true, name="firstname")
     */
    protected $firstname;

    /**
     * @var string $lastname
     * @ORM\Column(type="string", nullable=true, name="lastname")
     */
    protected $lastname;

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
     * @var datetime $exportdate
     * @ORM\Column(type="datetime", nullable=true, name="exportdate")
     */
    protected $exportdate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $log;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\FormType", inversedBy="leads")
     * @ORM\JoinColumn(name="form_type_id", referencedColumnName="id")
     */
    private $formType;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Form")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    private $form;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $utmcampaign;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $email = true;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ipadress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $userAgent;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Client", inversedBy="leads")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="Tellaw\LeadsFactoryBundle\Entity\Entreprise", inversedBy="leads")
     * @ORM\JoinColumn(name="entreprise_id", referencedColumnName="id")
     */
    private $entreprise;

    /**
     * @return mixed
     */
    public function getEntreprise()
    {
        return $this->entreprise;
    }

    /**
     * @param mixed $entreprise
     */
    public function setEntreprise($entreprise)
    {
        $this->entreprise = $entreprise;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }



    /**
     * @param mixed $formType
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;
    }

    /**
     * @return mixed
     */
    public function getFormType()
    {
        return $this->formType;
    }
    /**
     * @deprecated
     * @see setData
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $content
     */
    public function setContent($content)
    {
        $this->data = $content;
    }

    /**
     * @deprecated
     * @see getData
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
     */
    public function getContent()
    {
        return $this->data;
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
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param mixed $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param mixed $utmcampaign
     */
    public function setUtmcampaign($utmcampaign)
    {
        $this->utmcampaign = $utmcampaign;
    }

    /**
     * @return mixed
     */
    public function getUtmcampaign()
    {
        return $this->utmcampaign;
    }

    /**
     * Set formId
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $formId
     * @return Leads
     */
    public function setFormId(\Tellaw\LeadsFactoryBundle\Entity\Form $formId = null)
    {
        $this->formId = $formId;

        return $this;
    }

    /**
     * Get formId
     *
     * @return \Tellaw\LeadsFactoryBundle\Entity\Form 
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Set form
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @return Leads
     */
    public function setForm(\Tellaw\LeadsFactoryBundle\Entity\Form $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return \Tellaw\LeadsFactoryBundle\Entity\Form 
     */
    public function getForm()
    {
        return $this->form;
    }

	/**
	 * Set email
	 *
	 * @param string $email
	 * @return Leads
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
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
     * @param $source   source object from the search
     * @param $em   entity manager
     * @return $this
     */
    public function populateFromSearch ( $source, $em ) {

        $this->setId( $source->id );
        $this->setFirstname ( $source->firstname );
        $this->setLastname ( $source->lastname );
        $this->setData( json_encode($source->content) );
        $this->setStatus ( $source->status );
        $this->setExportdate( $source->exportdate );
        $this->setLog ( $source->log );
        $this->setUtmcampaign( $source->utmcampaign );
        $this->setTelephone( $source->telephone );
        $this->setCreatedAt( $source->createdAt );
        if ($source->form_id) $this->setForm( $em->getRepository('TellawLeadsFactoryBundle:Form')->find( $source->form_id ) );
        $this->setEmail( $source->email );
        if ($source->entreprise_id) $this->setEntreprise( $em->getRepository('TellawLeadsFactoryBundle:Entreprise')->find( $source->entreprise_id ) );
        if ($source->client_id) $this->setEntreprise( $em->getRepository('TellawLeadsFactoryBundle:Client')->find( $source->client_id ) );

        return $this;

    }

}
