<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Cron\CronExpression;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CronRunnerCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
            ->setName('leadsfactory:crontasks:run')
            ->setDescription('Runs Cron Tasks if needed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Tasks...</comment>');

        $schedulerUtils = $this->getContainer()->get('scheduler.utils');
        $schedulerUtils->updateDatabaseJobs();

        $now = new \DateTime();

        $this->output = $output;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $crontasks = $em->getRepository('TellawLeadsFactoryBundle:CronTask')->findAll();

        foreach ($crontasks as $crontask) {

            // If cron is enabled
            if ($crontask->getEnabled()) {

                // We must run this task if:
                // * time() is larger or equal to $nextrun
                //$run = @(time() >= $nextrun);


                if ($crontask->getNextrun() <= $now) {

                    $this->output = new BufferedOutput();
                    $output->writeln(sprintf('Running Cron Task <info>%s</info>', $crontask->getName()));

                    // Set $lastrun for this crontask
                    $crontask->setLastRun(new \DateTime());
                    try {

                        $commands = $crontask->getCommands();
                        foreach ($commands as $command) {

                            $output->writeln(sprintf('Executing command <comment>%s</comment>...', $command));
                            // Run the command
                            $this->runCommand($command);

                        }

                        $output->writeln('<info>SUCCESS</info>');
                        $crontask->setLog($this->output->fetch());
                        $crontask->setStatus(1);

                    } catch (\Exception $e) {

                        $output->writeln('<error>ERROR</error>');
                        $output->writeln('<error>' . $e->getMessage() . '</error>');
                        $output->writeln('<error>' . $e->getTraceAsString() . '</error>');
                        $crontask->setStatus(2);
                        $crontask->setLog($this->output->fetch() . "\r\n-----------------\r\n" . $e->getMessage() . "\r\n-----------------\r\n" . $e->getTraceAsString());

                    }

                    // Persist crontask
                    $em->persist($crontask);

                } else {
                    $output->writeln(sprintf('Skipping Cron Task <info>%s</info>', $crontask->getName()));
                }

                $em->flush();

            }

            // Get the last run time of this task, and calculate when it should run next
            $lastrun = $crontask->getLastRun() ? $crontask->getLastRun() : 0;
            $cron = CronExpression::factory($crontask->getCronexpression());
            $nextrun = $cron->getNextRunDate($lastrun);
            if (!$crontask->getNextrun() || $crontask->getNextrun() <= $now) {
                $crontask->setNextrun($nextrun);
                $em->persist($crontask);
                $em->flush();
            }

        }

        // Flush database changes
        $em->flush();

        $output->writeln('<comment>Done!</comment>');
    }

    private function runCommand($string)
    {
        // Split namespace and arguments
        $namespace = explode(' ', $string);
        $namespace = $namespace[0];

        // Set input
        $command = $this->getApplication()->find($namespace);
        $input = new StringInput($string);

        // Send all output to the console
        $returnCode = $command->run($input, $this->output);

        return $returnCode != 0;
    }

    function getExceptionTraceAsString($exception)
    {
        $rtn = "";
        $count = 0;
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = array();
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }
                $args = join(", ", $args);
            }
            $rtn .= sprintf("#%s %s(%s): %s(%s)\n",
                $count,
                $frame['file'],
                $frame['line'],
                $frame['function'],
                $args);
            $count++;
        }

        return $rtn;
    }


}
