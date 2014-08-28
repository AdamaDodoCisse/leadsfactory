<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Export;


use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;

class CSV extends AbstractMethod{

    /**
     * Process export to CSV file
     *
     * @todo log
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Export $jobs
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     */
    public function export($jobs, $form)
    {
        $fileName = 'export_'.$form->getFormType().'_'.time().'.csv';
        $path = $this->getExportPath();

        $handle = fopen($path.DIRECTORY_SEPARATOR.$fileName, 'w+');
        if($handle === false){
            throw new \Exception("Impossible d'ouvrir ou créer le fichier ".$fileName);
        }

        $exportUtils = $this->getContainer()->get('export_utils');

        foreach($jobs as $job){
            $lead = $job->getLead();
            $data = json_decode($lead->getData(), true);
            $status = fputcsv($handle, $data) ? $exportUtils::$_EXPORT_SUCCESS : $exportUtils->getErrorStatus($job);
            $lead->setStatus($status);
            $log = ($status != $exportUtils::$_EXPORT_SUCCESS) ? "Erreur lors de l'édition du fichier CSV" : 'Succès';

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($lead);
            $em->flush();

            $exportUtils->updateJob($job, $status, $log);
        }
        fclose($handle);
    }
} 