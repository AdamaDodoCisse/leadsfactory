<?php

namespace Weka\LeadsExportBundle\Utils\Gotowebinar;


use Doctrine\ORM\EntityManager;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElementRepository;

class BaseMapping
{
    /** @var ReferenceListElementRepository $list_element_repository */
    protected $list_element_repository;

	/** @var EntityManager */
	protected $em;

	public function __construct(EntityManager $entityManager, ReferenceListElementRepository $list_element_repository)
	{
		$this->em = $entityManager;
        $this->list_element_repository = $list_element_repository;
	}

    public function getMapping()
    {
        return array(
            "firstName"		            => 'firstName',
            "lastName"                  => 'lastName',
            "email"	                    => 'email',
            "address"                   => 'address',
            "city"                      => 'ville',
            "state"		                => '',
            "zipCode"		            => 'zip',
            "country"		            => 'pays',
            "phone"		                => 'phone',
            "timeZone"		            => '',
            "industry"		            => '',
            "organization"		        => 'etablissement',
            "jobTitle"		            => 'fonction',
            "purchasingTimeFrame"		=> '',
            "roleInPurchaseProcess"		=> '',
            "numberOfEmployees"		    => '',
            "status"		            => ''
        );
    }

	public function getCountry($data)
	{
		return 'France';
	}

	public function getTimeZone($data)
	{
		return 'Europe/Paris';
	}

	/*public function getStatus($data)
	{
		return 'APPROVED';
	}*/
}
