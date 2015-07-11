<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
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
class PreferencesUtils implements ContainerAwareInterface {

    public static $_SCOPE_GLOBAL = "GLOBAL";

    public function __construct() {

    }

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     *
     */
    public function setContainer(ContainerInterface $container = null) {

        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    public function getUserPreferenceByKey ( $key, $scope = "", $notifyIfNotFound = true )
    {

        if ($scope == "") {
            $preference = $this->container->get('leadsfactory.preference_repository')->findOneByKeyval($key);
        } else if ( $scope == PreferencesUtils::$_SCOPE_GLOBAL ) {
            $preference = $this->container->get('leadsfactory.preference_repository')->findOneByKeyvalAndScope ($key, "");
        }else {
            $preference = $this->container->get('leadsfactory.preference_repository')->findOneByKeyvalAndScope ($key, $scope);
        }

        if ($preference != null ) {
            return $preference->getValue();
        } else {
            return null;
        }

    }

}
