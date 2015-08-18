<?php
namespace Tellaw\LeadsFactoryBundle\Utils\ScheduledJobs;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Tellaw\LeadsFactoryBundle\Utils\IScheduledJob;

class StatusHistoryScheduler implements IScheduledJob
{

    public function getExpression()
    {
        return "1 * * * * *";
    }

    public function getName()
    {
        return "Core_AlertStatusHistory_Job";
    }

    public function getCommands()
    {
        return array ('leadsfactory:statusHistory:update');
    }

    public function getEnabled()
    {
        return true;
    }

}
