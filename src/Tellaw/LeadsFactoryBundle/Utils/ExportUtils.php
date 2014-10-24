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

    /**
     * Email notifications settings
     */
    const NOTIFICATION_DEFAULT_FROM = 'leadsfactory@domain.com';
    const NOTIFICATION_DEFAULT_TEMPLATE = 'emails:notification_default.html.twig';

    /**
     * @var array
     */
    private $_methods;

    /**
     * @var string
     */
    private $_defaultCronExp;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;


    public function __construct()
    {
        $this->_methods = array();
        $this->_defaultCronExp = "0 * * * *";
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
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
     * @param $config
     * @return bool
     */
    public function hasScheduledExport($config)
    {
        return (isset($config['export']) && is_array($config['export'])) ? true : false;
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
        foreach($config['export'] as $method=>$methodConfig){

            $job = new Export();

            if(!$this->isValidExportMethod($method)){
                $job->setLog('Méthode d\'export invalide');
                $this->getContainer()->get('export.logger')->info('Méthode d\'export invalide (formulaire ID '.$lead->getForm()->getId().')');
            }

            $job->setMethod($method);
            $job->setLead($lead);
            $job->setForm($lead->getForm());
            $job->setStatus($lead->getStatus());
            $job->setCreatedAt(new \DateTime());
            $job->setScheduledAt($this->getScheduledDate($methodConfig));

            try{
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($job);
                $em->flush();
                $this->getContainer()->get('export.logger')->info('Job export (ID '.$job->getId().') créé avec succès');

            }catch (Exception $e) {
                $this->getContainer()->get('export.logger')->error($e->getMessage());
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
        $cronExp = (isset($methodConfig['cron'])) ? $methodConfig['cron'] : $this->_defaultCronExp;
        $cron = CronExpression::factory($cronExp);
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
        foreach($config['export'] as $method=>$methodConfig){

            if(!$this->isValidExportMethod($method)){
                throw new \Exception('Méthode d\'export "'.$method.'" invalide');
                continue;
            }
            $jobs = $this->getExportableJobs($form, $method, $methodConfig);

            if(count($jobs))
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
        }catch (\Exception $e) {
            $this->getContainer()->get('export.logger')->error($e->getMessage());
        }
    }

    /**
     * @param \Tellaw\LeadsFactoryBundle\Entity\Lead $lead
     * @param int $status
     * @param string $log
     * @param DateTime $exportDate
     */
    public function updateLead($lead, $status, $log, $exportDate = null)
    {
        $exportDate = is_null($exportDate) ? new \DateTime() : $exportDate;

        $lead->setStatus($status);
        $lead->setLog($log);
        $lead->setExportdate($exportDate);

        try{
            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($lead);
            $em->flush();
        }catch (\Exception $e) {
            $this->getContainer()->get('export.logger')->error($e->getMessage());
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

