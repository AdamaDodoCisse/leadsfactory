<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Export;


use Symfony\Component\Config\Definition\Exception\Exception;

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

        foreach($leads as $lead){
            $data = json_decode($lead->getData(), true);
            fputcsv($handle, $data) ? $lead->setStatus($lead::$_STATUS_EXPORT_DONE) : $lead->setStatus($lead::$_STATUS_EXPORT_FAILED);

            try{
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($lead);
                $em->flush();
            }catch (Exception $e) {

            }
        }
        fclose($handle);
    }
} 