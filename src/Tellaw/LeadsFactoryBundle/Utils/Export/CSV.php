<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Export;


use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;

class CSV extends AbstractExportMethod{

    /**
     * Process export to CSV file
     *
     * @todo log
     *
     * @param $leads
     * @param $form
     */
    public function export($leads, $form)
    {
        $fileName = 'export_'.$form->getFormType().'_'.time().'.csv';
        $path = $this->getExportPath();

        $handle = fopen($path.DIRECTORY_SEPARATOR.$fileName, 'w+');
        if($handle === false){
            //Log error
        }

        foreach($leads as $lead){
            $data = json_decode($lead->getData(), true);
            $status = fputcsv($handle, $data) ? $lead::$_EXPORT_SUCCESS : $lead->getNewErrorStatus();
            $lead->setStatus($status);
            try{
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($lead);
                $em->flush();
            }catch (Exception $e) {
                //Error
            }

            $this->updateHistory($lead, $form);
        }
        fclose($handle);
    }
} 