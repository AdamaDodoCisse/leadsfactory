<?php

namespace Weka\LeadsExportBundle\Utils;

use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;


class Edeal extends AbstractMethod{

	public function __construct($credentials)
    {
    }

    /**
     * Process export
     *
     * @param array $jobs
     * @param Form $form
     */
    public function export($jobs, $form)
    {
        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getContainer()->get('export.logger');
	    $scope = $form->getScope()->getCode();

	    /** @var Export $job */
	    foreach($jobs as $job){

            $logger->info('Export EDEAL désactivé');
            $exportUtils->updateJob($job, $exportUtils::$_EXPORT_NOT_SCHEDULED, 'Export Edeal désactivé');
            $exportUtils->updateLead($job->getLead(), $exportUtils::$_EXPORT_NOT_SCHEDULED, 'Export Edeal désactivé');

        }
    }

}
