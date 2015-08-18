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

    public static $_PRIORITY_OPTIONNAL = "1";
    public static $_PRIORITY_REQUIRED = "2";

    public static $_REGISTERED_KEYS  = array();

    public function __construct() {

    }

    /**
     *
     * Function used to register a new preference service.
     *
     * @param $key              // Is the key used to load the preference
     * @param $description      // Literal desription of the service, used for error feedbacks
     * @param $priority         // Priority defines whenever key is required to exist in the application instance or not
     * @param $scope            // Scope defines the application scope.
     * @param $is_uniq          // Is uniq defines that if the same key is tryied to be registered twice, an Exception will be raised
     * @throws \Exception
     */
    public static function registerKey (    $key,
                                            $description,
                                            $priority,
                                            $scope,
                                            $is_uniq
                                        ) {

        if ( $is_uniq && array_key_exists( $key, PreferencesUtils::$_REGISTERED_KEYS )) {
            throw new \Exception ("Registered preference key must be unique, but is already declared : ".$key);
        }

        PreferencesUtils::$_REGISTERED_KEYS [ $key ] = array (  "key" => $key,
                                                                "description" => $description,
                                                                "priority" => $priority,
                                                                "scope" => $scope
                                                            );

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
