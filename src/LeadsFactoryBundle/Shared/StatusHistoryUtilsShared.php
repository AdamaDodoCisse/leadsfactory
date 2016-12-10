<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 08:34
 */

namespace LeadsFactoryBundle\Shared;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StatusHistoryUtilsShared implements ContainerAwareInterface
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
    public function setContainer(ContainerInterface $container = null)
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
    public function getCurrentStatusForMonitor($monitorId)
    {


    }

    /**
     * @param $monitorId
     * This method will return a duration about the current status of a monitor.
     */
    public function getActualStatusDurationForMonitor($monitorId)
    {

    }


}
