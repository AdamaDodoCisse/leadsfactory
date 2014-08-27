<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Cron\CronExpression;

class ExportUtils{

    const CSV_METHOD = 'csv';

    /**
     * @var array
     */
    static $export_methods = array(
        self::CSV_METHOD
    );

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /*
     *
     */
    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Check if form is configured for export
     *
     * @param json $exportConfig
     * @return bool
     */
    public function hasScheduledExport($exportConfig)
    {
        $exportConfig = json_decode(trim($exportConfig), true);
        foreach(self::$export_methods as $type){
            if(array_key_exists($type, $exportConfig)){
                return true;
            }
        }
        return false;
    }

    /**
     * Check if export method is valid
     *
     * @param $method
     * @return bool
     */
    public function isValidExportMethod($method)
    {
        return in_array($method, self::$export_methods) ? true : false;
    }

    /**
     * Create export job
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Leads $lead
     */
    public function createJob($lead)
    {
        $config = $lead->getForm()->getConfig();
        foreach($config as $method=>$methodConfig){

            //todo check method availability

            $new = new Export();
            $new->setType($method);
            $new->setLead($lead);
            $new->setForm($lead->getForm());
            $new->setStatus($lead->getStatus());
            $new->setCreatedAt(new \DateTime());
            $new->setScheduledAt($this->getScheduledDate($methodConfig));

            try{
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($new);
                $em->flush();
            }catch (Exception $e) {
                echo $e->getMessage();
                //Error
            }
        }
    }

    /**
     * Return job execution scheduled date
     *
     * @param array $methodConfig
     * @return \DateTime
     */
    protected function getScheduledDate($methodConfig)
    {
        $cron = CronExpression::factory($methodConfig['cron']);
        return $cron->getNextRunDate($this->getMinDate($methodConfig));
    }

    /**
     * Return scheduled min date taking into account the time gap possibly set in config
     *
     * @param array $methodConfig
     * @return \DateTime|string
     */
    protected function getMinDate($methodConfig)
    {
        if(!isset($methodConfig['gap']) || trim($methodConfig['gap']) == '')
            return 'now';

        $minDate = new \DateTime();
        return $minDate->add(new \DateInterval('PT'.trim($methodConfig['gap']).'M'));
    }

    /**
     * Launches export for each configured export methods
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     */
    public function export($form)
    {
        $config = $form->getConfig();
        foreach($config as $method=>$methodConfig){
            $jobs = $this->getExportableJobs($form, $method, $methodConfig);
            $this->getContainer()->get($method.'_method')->export($jobs, $form);
        }
    }

    /**
     * Retrieve exportable jobs for a given form and export method
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     * @param string $method
     * @param array $methodConfig
     * @return array
     */
    protected function getExportableJobs($form, $method, $methodConfig)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $query = $em->createQuery(
            'SELECT j
            FROM TellawLeadsFactoryBundle:Export j
            WHERE j.form = :form
              AND j.method = :method
              AND j.scheduled_at <= :now
              AND j.status NOT IN (:status)'
        );
        $query->setParameters(array(
            'form'      => $form,
            'method'    => $method,
            'now'       => new \DateTime(),
            'status'    => '1'
        ));
        $jobs = $query->getResult();
        return $jobs;
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\Job $job
     * @param int $status
     */
    public function updateJob($job, $status)
    {
        $job->setStatus($status);
        $job->setExecutedAt(new \DateTime());

        try{
            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($job);
            $em->flush();
        }catch (Exception $e) {
            //Error
        }
    }
}

