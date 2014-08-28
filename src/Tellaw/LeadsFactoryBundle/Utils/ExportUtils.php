<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Cron\CronExpression;

class ExportUtils{

    public static $_EXPORT_NOT_PROCESSED = 0;
    public static $_EXPORT_SUCCESS = 1;
    public static $_EXPORT_ONE_TRY_ERROR = 2;
    public static $_EXPORT_MULTIPLE_ERROR = 3;
    public static $_EXPORT_NOT_SCHEDULED = 4;

    private $_methods;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;


    public function __construct()
    {
        $this->_methods = array();
    }

    /**
     *
     */
    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    public function addMethod(AbstractMethod $method, $alias)
    {
        $this->_methods[$alias] = $method;
    }

    public function getMethod($alias)
    {
        return $this->_methods[$alias];
    }

    /**
     * Check if form is configured for export
     *
     * @param string $exportConfig
     * @return bool
     */
    public function hasScheduledExport($exportConfig)
    {
        return (is_array($exportConfig)) ? true : false;
    }

    /**
     * Check if export method is valid
     *
     * @param $method
     * @return bool
     */
    public function isValidExportMethod($method)
    {
        return array_key_exists($method, $this->_methods) ? true : false;
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

            if(!$this->isValidExportMethod($method)){
                throw new \Exception('Méthode d\'export "'.$method.'" invalide, le job n\'a pas été créé');
                continue;
            }

            $new = new Export();
            $new->setMethod($method);
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

            if(!$this->isValidExportMethod($method)){
                throw new \Exception('Méthode d\'export "'.$method.'" invalide');
                continue;
            }
            $jobs = $this->getExportableJobs($form, $method, $methodConfig);
            $this->getMethod($method)->export($jobs, $form);
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
            'status'    => self::$_EXPORT_SUCCESS
        ));
        return $query->getResult();
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\Export $job
     * @param $status
     * @param string $log
     */
    public function updateJob($job, $status, $log='')
    {
        $job->setStatus($status);
        $job->setExecutedAt(new \DateTime());
        $job->setLog($log);

        try{
            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($job);
            $em->flush();
        }catch (Exception $e) {
            //Error
        }
    }

    /**
     * Return error status
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Export $job
     * @return int
     */
    public function getErrorStatus($job){
        if($job->getStatus() == self::$_EXPORT_NOT_PROCESSED || is_null($job->getStatus())){
            return self::$_EXPORT_ONE_TRY_ERROR;
        }else{
            return self::$_EXPORT_MULTIPLE_ERROR;
        }
    }
}

