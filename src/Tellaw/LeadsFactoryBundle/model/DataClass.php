<?php

namespace Tellaw\MonitorUBundle\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DataClass {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected $name;
    protected $description;
    protected $configuration;
    protected $key;
    protected $formid;

}
