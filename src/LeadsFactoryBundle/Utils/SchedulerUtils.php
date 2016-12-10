<?php
namespace LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use LeadsFactoryBundle\Shared\SchedulerUtilsShared;


/**
 * Class StatusHistoryUtils
 * @package LeadsFactoryBundle\Utils
 *
 * Utils class which makes easier to manipulate StatusHistory objects
 */
class SchedulerUtils extends SchedulerUtilsShared
{

    public $scheduledJobs = array();

    public $organisedScheduledJobs = null;

    public function __construct()
    {
        //parent::__construct();
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    public function addScheduledJob($id)
    {
        $this->scheduledJobs[] = $id;
    }


}
