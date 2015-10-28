<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 28/10/15
 * Time: 12:07
 */

namespace Tellaw\LeadsFactoryBundle\Utils\ScheduledJobs;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Tellaw\LeadsFactoryBundle\Utils\IScheduledJob;

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
        return array (  'leadsfactory:export:reset ti',
                        'leadsfactory:export:reset weka',
                        'leadsfactory:export:reset comundi');
    }

    public function getEnabled()
    {
        return true;
    }
}