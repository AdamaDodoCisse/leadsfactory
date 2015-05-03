<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Tellaw\LeadsFactoryBundle\Entity\CronTask;


/**
 * Class StatusHistoryUtils
 * @package Tellaw\LeadsFactoryBundle\Utils
 *
 * Utils class which makes easier to manipulate StatusHistory objects
 */
class SchedulerUtils implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    private $scheduledJobs = array();

    private $organisedScheduledJobs = null;

    public function __construct()
    {
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

    public function addScheduledJob ( $id ) {
        $this->scheduledJobs[] = $id;
    }

    /**
     * Return an array with job's name as KEY and instance of job as array entry
     * @return array of OrganizedScheduledJobs
     */
    public function getScheduledJobs () {

        if (!$this->organisedScheduledJobs) {
            foreach ($this->scheduledJobs as $scheduledJob) {
                $job = $this->getContainer()->get( $scheduledJob );
                $this->organisedScheduledJobs[ $job->getName() ] = array ("id" => $scheduledJob, "job" => $job);
            }
        }
        return $this->organisedScheduledJobs;
    }

    public function updateDatabaseJobs () {

        $jobs = $this->getScheduledJobs();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $cronTasksRepository = $em->getRepository('TellawLeadsFactoryBundle:CronTask');

        foreach ( $jobs as $name => $idAndJobArray ) {

            $job = $idAndJobArray["job"];
            $cronTaskJob = $cronTasksRepository->findOneByName ( $name );

            if ( !$cronTaskJob ) {

                $cronTaskJob = new CronTask();
                $cronTaskJob->setName( $job->getName() );
                $cronTaskJob->setCronexpression( $job->getExpression() );
                $cronTaskJob->setCommands( $job->getCommands() );
                $cronTaskJob->setEnabled( $job->getEnabled() );
                $now = new \DateTime();
                $cronTaskJob->setCreatedAt( $now );
                $cronTaskJob->setModifiedAt( $now );
                $cronTaskJob->setServiceName( $idAndJobArray["id"] );
                $em->persist($cronTaskJob);

            } else {

                $updateNeed = false;
                /*
                 * Decided that cronexpression could be overrided by UI Admin
                 *
                if ( $job->getExpression() != $cronTaskJob->getCronexpression() ) {
                    $cronTaskJob->setCronexpression( $job->getExpression() );
                    $updateNeed = true;
                }
                */
                if ( $job->getCommands() != $cronTaskJob->getCommands() ) {
                    $cronTaskJob->setCommands( $job->getCommands() );
                    $updateNeed = true;
                }

                if ($updateNeed) {
                    $now = new \DateTime();
                    $cronTaskJob->setModifiedAt( $now );
                    $em->persist($cronTaskJob);
                    $em->flush();
                }

            }

        }

    }

}