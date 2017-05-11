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
class EntreprisePersonReference
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\ManyToOne(targetEntity="Entreprise", inversedBy="persons") */
    protected $entreprise;

    /** @ORM\ManyToOne(targetEntity="Person", inversedBy="entreprises") */
    protected $person;

    protected $position;


}
