<?php
namespace Tellaw\LeadsFactoryBundle\Utils\ScheduledJobs;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Tellaw\LeadsFactoryBundle\Utils\IScheduledJob;

class SearchWatchdogScheduler implements IScheduledJob
{

    public function getExpression()
    {
        return "*/5 * * * *";
    }

    public function getName()
    {
        return "Core_SearchWatchdog_Job";
    }

    public function getCommands()
    {
        return array('leadsfactory:search:watchdog');
    }

    public function getEnabled()
    {
        return true;
    }

}
