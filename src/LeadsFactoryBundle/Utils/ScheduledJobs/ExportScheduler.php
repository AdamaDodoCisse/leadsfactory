<?php
namespace LeadsFactoryBundle\Utils\ScheduledJobs;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use LeadsFactoryBundle\Utils\IScheduledJob;

class ExportScheduler implements IScheduledJob
{

    public function getExpression()
    {
        return "*/15 * * * * *";
    }

    public function getName()
    {
        return "Core_Export_Job";
    }

    public function getCommands()
    {
        return array('leadsfactory:export:leads');
    }

    public function getEnabled()
    {
        return true;
    }

}
