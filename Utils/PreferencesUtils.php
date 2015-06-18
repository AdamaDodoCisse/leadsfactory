<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Doctrine\ORM\QueryBuilder;
use Tellaw\LeadsFactoryBundle\Entity\FormType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AlertUtils
 *
 * This class intends to provide methods to check the status of objects and calculate its values
 *
 * @package Tellaw\LeadsFactoryBundle\Utils
 */
class PreferencesUtils {

    /** @var EntityManagerInterface */
    protected $entity_manager;

    public function __construct(EntityManagerInterface $entity_manager) {
        $this->entity_manager = $entity_manager;
    }

    public function getUserPreferenceByKey ( $key ) {

    }

    public function setUserPreference ( $key, $value ) {

    }

    public function getApplicationPreferenceByKey ( $key ) {

    }

    public function setApplicationPreference ( $key, $value ) {

    }

}
