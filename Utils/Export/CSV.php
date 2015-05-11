<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Export;

use Symfony\Component\Validator\Constraints\DateTime;

class CSV extends AbstractMethod{

    /**
     * Process export to CSV file
     *
     * @param array $jobs
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     */
    public function export($jobs, $form)
    {
        $fileName = 'export_'.$form->getCode().'_'.time().'.csv';
        $path = $this->getExportPath();

        $logger = $this->getContainer()->get('export.logger');

        $handle = fopen($path.DIRECTORY_SEPARATOR.$fileName, 'w+');
        if($handle === false){
            $logger->error("Export CSV : impossible d'ouvrir ou créer le fichier ".$fileName);
        }

        $exportUtils = $this->getContainer()->get('export_utils');

        foreach($jobs as $job){
            $lead = $job->getLead();
            $data = json_decode($lead->getData(), true);
            $status = fputcsv($handle, $data) ? $exportUtils::$_EXPORT_SUCCESS : $exportUtils->getErrorStatus($job);
            $lead->setStatus($status);
            $log = ($status != $exportUtils::$_EXPORT_SUCCESS) ? "Job export (ID ".$job->getId().") : erreur lors de l'édition du fichier CSV" : "Job export (ID ".$job->getId().") : exporté avec succès";

            $logger->info($log);

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($lead);
            $em->flush();

            $exportUtils->updateJob($job, $status, $log);
        }
        fclose($handle);
    }
} 