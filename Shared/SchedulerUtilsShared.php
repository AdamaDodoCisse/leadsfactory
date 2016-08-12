<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 08:00
 */

namespace Tellaw\LeadsFactoryBundle\Shared;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tellaw\LeadsFactoryBundle\Entity\CronTask;

class SchedulerUtilsShared implements ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Return an array with job's name as KEY and instance of job as array entry
     * @return array of OrganizedScheduledJobs
     */
    public function getScheduledJobs()
    {

        if (!$this->organisedScheduledJobs) {
            foreach ($this->scheduledJobs as $scheduledJob) {
                $job = $this->getContainer()->get($scheduledJob);
                $this->organisedScheduledJobs[$job->getName()] = array("id" => $scheduledJob, "job" => $job);
            }
        }

        return $this->organisedScheduledJobs;
    }

    public function updateDatabaseJobs()
    {

        $jobs = $this->getScheduledJobs();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $cronTasksRepository = $em->getRepository('TellawLeadsFactoryBundle:CronTask');

        foreach ($jobs as $name => $idAndJobArray) {

            $job = $idAndJobArray["job"];
            $cronTaskJob = $cronTasksRepository->findOneByName($name);

            if (!$cronTaskJob) {

                $cronTaskJob = new CronTask();
                $cronTaskJob->setName($job->getName());
                $cronTaskJob->setCronexpression($job->getExpression());
                $cronTaskJob->setCommands($job->getCommands());
                $cronTaskJob->setEnabled($job->getEnabled());
                $now = new \DateTime();
                $cronTaskJob->setCreatedAt($now);
                $cronTaskJob->setModifiedAt($now);
                $cronTaskJob->setServiceName($idAndJobArray["id"]);
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
                if ($job->getCommands() != $cronTaskJob->getCommands()) {
                    $cronTaskJob->setCommands($job->getCommands());
                    $updateNeed = true;
                }

                if ($updateNeed) {
                    $now = new \DateTime();
                    $cronTaskJob->setModifiedAt($now);
                    $em->persist($cronTaskJob);
                    $em->flush();
                }

            }

        }

    }


}
