<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\Field
 * 
 * @ORM\Entity(repositoryClass="Tellaw\LeadsFactoryBundle\Entity\FieldRepository")
 */
class Field {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $code
     * @ORM\Column(type="string", nullable=true, name="name")
     */
    protected $code;

    /**
     * @var longtext $description
     * @ORM\Column(type="text", nullable=true, name="description")
     */
    protected $description;

    /**
     * @var string $testValue
     * @ORM\Column(type="string", nullable=true, name="name")
     */
    protected $testValue;
}
