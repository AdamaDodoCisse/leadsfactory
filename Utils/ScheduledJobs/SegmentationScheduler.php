<?php
namespace Tellaw\LeadsFactoryBundle\Utils\ScheduledJobs;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Tellaw\LeadsFactoryBundle\Utils\IScheduledJob;

class SegmentationScheduler implements IScheduledJob
{

    public function getExpression()
    {
        return "*/15 * * * *";
    }

    public function getName()
    {
        return "Core_SegmentationExport_Job";
    }

    public function getCommands()
    {
        return array('leadsfactory:export:segmentation');
    }

    public function getEnabled()
    {
        return true;
    }

}
