<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 28/10/15
 * Time: 12:07
 */

namespace LeadsFactoryBundle\Utils\ScheduledJobs;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use LeadsFactoryBundle\Utils\IScheduledJob;

class ResetExportScheduler implements IScheduledJob
{
    public function getExpression()
    {
        return "0 0 * * *";
    }

    public function getName()
    {
        return "Core_ResetExport_Job";
    }

    public function getCommands()
    {
        return array('leadsfactory:export:reset all');
    }

    public function getEnabled()
    {
        return true;
    }
}
