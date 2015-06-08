<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;


/**
 * Class StatusHistoryUtils
 * @package Tellaw\LeadsFactoryBundle\Utils
 *
 * Utils class which makes easier to manipulate StatusHistory objects
 */
class StatusHistoryUtils implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;


    public function __construct()
    {
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer (ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @param $monitorId
     *
     * Method used to find the last monitored status for monitor.
     * Method will find last recorded status using a date MAX.
     */
    public function getCurrentStatusForMonitor ( $monitorId ) {


    }

    /**
     * @param $monitorId
     * This method will return a duration about the current status of a monitor.
     */
    public function getActualStatusDurationForMonitor ( $monitorId ) {

    }


}