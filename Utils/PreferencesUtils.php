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

    public static $_SCOPE_NOT_REQUIRED = false;
    public static $_SCOPE_REQUIRED = false;

    public static $_REGISTERED_KEYS  = array();

    private $servicesDeclaringPreferences = array();

    public function __construct() {

        // Used in StatusHistoryUpdateCommand
        $this->registerKey( "CORE_STATUS_HISTORY_EMAIL",
                            "Email de notification des problèmes d'exports, configurable par scopes",
                            PreferencesUtils::$_PRIORITY_OPTIONNAL);

    }

    public function addMethod ( $service ) {
        $this->servicesDeclaringPreferences[] = $service;
    }

    /**
     *
     * Function used to register a new preference service.
     *
     * @param $key              // Is the key used to load the preference
     * @param $description      // Literal desription of the service, used for error feedbacks
     * @param $priority         // Priority defines whenever key is required to exist in the application instance or not
     * @param $scope            // Define true if preference must be set for scopes, false, if global scope is enough.
     * @param $is_uniq          // Is uniq defines that if the same key is tryied to be registered twice, an Exception will be raised (default true)
     * @throws \Exception
     */
    public static function registerKey (    $key,
                                            $description,
                                            $priority,
                                            $scope = false,
                                            $is_uniq = true
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
        if (!array_key_exists( $key, PreferencesUtils::$_REGISTERED_KEYS ) ) {
            throw new \Exception ("Usage of a non registered preference key : ".$key." - Please use PreferencesUtils::registerKey to register the key before using it");
        }

        if ($scope == "") {
            $preference = $this->container->get('leadsfactory.preference_repository')->findOneByKeyval($key);
        } else if ( $scope == PreferencesUtils::$_SCOPE_GLOBAL ) {
            $preference = $this->container->get('leadsfactory.preference_repository')->findByKeyAndScope ($key, "");
        }else {
            $preference = $this->container->get('leadsfactory.preference_repository')->findByKeyAndScope ($key, $scope);
        }

        if ($preference != null ) {
            return $preference->getValue();
        } else {
            return null;
        }

    }

    public function getValuesForKey ( $key ) {

        $preferences = $this->container->get('leadsfactory.preference_repository')->findByKey($key);
        return $preferences;

    }

    public function getListOfRequiredPreferences () {

        $keys = array();
        foreach ( PreferencesUtils::$_REGISTERED_KEYS as $key => $attributes) {

            if ( $attributes["priority"] == PreferencesUtils::$_PRIORITY_REQUIRED ) {
                $keys[$key] = $attributes;
            }

        }

        return $keys;

    }

    public function getListOfOptionnalPreferences () {

        $keys = array();
        foreach ( PreferencesUtils::$_REGISTERED_KEYS as $key => $attributes) {

            if ( $attributes["priority"] == PreferencesUtils::$_PRIORITY_OPTIONNAL ) {
                $keys[$key] = $attributes;
            }

        }

        return $keys;

    }

}
