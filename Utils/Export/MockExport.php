<?php

namespace Tellaw\LeadsFactoryBundle\Utils\Export;

use Symfony\Component\Config\Definition\Exception\Exception;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;

/**
 * Class MockExport
 * @package Weka\LeadsExportBundle\Utils
 *
 * This class is a mock built for unit testing and dev ONLY.
 *
 */
class MockExport extends AbstractMethod
{

    public $_expectedResult = null;

    public static $_EXPORT_ERROR = -1;
    public static $_EXPORT_SUCCESS = 1;
    public static $_EXPORT_THROW_UNHANDLED_EXCEPTION = 2;

    public function __construct($expectedResult)
    {
        $this->_expectedResult = $expectedResult;
    }

    public function export($jobs, $form)
    {

        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getContainer()->get('export.logger');

        $logger->info('Mock Export start : ' . $form->getName());

        foreach ($jobs as $job) {

            if ($this->_expectedResult == MockExport::$_EXPORT_SUCCESS) {

                $status = ExportUtils::$_EXPORT_SUCCESS;
                $log = "Status du mock : Success";

            } else if ($this->_expectedResult == MockExport::$_EXPORT_ERROR) {

                //$status = ExportUtils::$_EXPORT_ONE_TRY_ERROR;
                $status = $exportUtils->getErrorStatus($job);
                $log = "Status du mock : Error : " . $status;
                $this->notifyOfExportIssue($log, $form, $job, $status);

            } else if ($this->_expectedResult == MockExport::$_EXPORT_THROW_UNHANDLED_EXCEPTION) {

                throw new Exception ("Export Mock demo for Unhandled Exception thrown by export task");
                $log = "Status du mock : Exception!";

            }

            // Writing result
            // Remember to update JOB and parent lead
            $exportUtils->updateJob($job, $status, $log);
            $exportUtils->updateLead($job->getLead(), $status, $log);
            $logger->info($log);

        }
    }

}
