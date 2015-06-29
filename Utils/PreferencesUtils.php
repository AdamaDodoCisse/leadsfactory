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

    /** @var EntityManagerInterface */
    protected $entity_manager;

    public function __construct(EntityManagerInterface $entity_manager) {
        $this->entity_manager = $entity_manager;
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

    public function getUserPreferenceByKey ( $key, $scope = "" ) {

        if ($scope == "") {
            $preferenceCollection = $this->container->getRepository('TellawLeadsFactoryBundle:Preference')->findByKey ($key);
        } else {
            $preferenceCollection = $this->container->getRepository('TellawLeadsFactoryBundle:Preference')->findByKeyAndScope ($key, $scope);
        }

        return $preferenceCollection;

    }

}
