<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Utils\Export\AbstractMethod;
use Cron\CronExpression;
use Tellaw\LeadsFactoryBundle\Entity\ClientEmailRepository;

class ExportUtils implements ContainerAwareInterface
{
    public static $_EXPORT_NOT_PROCESSED = 0;
    public static $_EXPORT_SUCCESS = 1;
    public static $_EXPORT_ONE_TRY_ERROR = 2;
    public static $_EXPORT_MULTIPLE_ERROR = 3;
    public static $_EXPORT_NOT_SCHEDULED = 4;
    const EXPORT_EMAIL_NOT_CONFIRMED = 5;

    /**
     * Email notifications settings
     */
    const NOTIFICATION_DEFAULT_FROM = 'leadsfactory@domain.com';
    const NOTIFICATION_DEFAULT_TEMPLATE = 'emails:notification_default.html.twig';

    /**
     * @var array
     */
    private $_methods = array();

    /**
     * @var string
     */
    private $_defaultCronExp = "0 * * * *";

    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var  ClientEmailRepository */
    private $client_email_repository;


    public function __construct(ClientEmailRepository $client_email_repository)
    {
        $this->client_email_repository = $client_email_repository;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer (ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
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
     * @param Leads $lead
     */
    public function createJob($lead)
    {
        $logger = $this->getContainer()->get('export.logger');

        $config = $lead->getForm()->getConfig();
        foreach($config['export'] as $method=>$methodConfig){

            $job = new Export();

            if(!$this->isValidExportMethod($method)){
                $job->setLog('Méthode d\'export invalide');
                $logger->info('Méthode d\'export invalide (formulaire ID '.$lead->getForm()->getId().')');
            }

            $job->setMethod($method);
            $job->setLead($lead);
            $job->setForm($lead->getForm());
            $status = $this->getInitialExportStatus($lead, array( 'method' => $method, 'method_config' => $methodConfig));
            $job->setStatus($status);
            $job->setCreatedAt(new \DateTime());
            $job->setScheduledAt($this->getScheduledDate($methodConfig));

            try{
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($job);
                $em->flush();
                $logger->info('Job export (ID '.$job->getId().') créé avec succès');

            }catch (Exception $e) {
                $logger->error($e->getMessage());
                //Error
            }
        }
    }

    /**
     * @param Leads $lead
     */
    protected function getInitialExportStatus($lead, $config)
    {
	    $method_config = $config['method_config'];
	    $methodObject = $this->getMethod($config['method']);

        if (
            array_key_exists('if_email_validated', $method_config)
            && $method_config['if_email_validated'] === true
        ) {
            $email = $lead->getEmail();
	        $validated = $methodObject->isEmailValidated($lead, $email);
            if ($validated) {
                return self::$_EXPORT_NOT_PROCESSED;
            } else {
                return self::EXPORT_EMAIL_NOT_CONFIRMED;
            }
        } else {
            return self::$_EXPORT_NOT_PROCESSED;
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
     * Launches export for all configured methods
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\Form $form
     */
    public function export($form)
    {
        $logger = $this->getContainer()->get('export.logger');

        $config = $form->getConfig();

        if(!isset($config['export']))
            return;

        foreach($config['export'] as $method=>$methodConfig){

            if(!$this->isValidExportMethod($method)){
                $logger->error('Méthode d\'export "'.$method.'" invalide');
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
            'status'    => array(self::$_EXPORT_SUCCESS, self::EXPORT_EMAIL_NOT_CONFIRMED, self::$_EXPORT_NOT_SCHEDULED)
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

