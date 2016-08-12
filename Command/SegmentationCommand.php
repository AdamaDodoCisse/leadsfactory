<?php

namespace Tellaw\LeadsFactoryBundle\Command;

use Cron\CronExpression;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Utils\ElasticSearchUtils;
use Tellaw\LeadsFactoryBundle\Utils\SegmentUtils;


class SegmentationCommand extends ContainerAwareCommand
{


    protected function configure()
    {
        $this->setName('leadsfactory:export:segmentation')
            ->setDescription('Segmentation : Export configured segments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Segmentation exports Tasks...</comment>');

        $now = new \DateTime();
        $this->output = $output;

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $segments = $this->getContainer()->get('leadsfactory.mkgsegment_repository')->findAll();

        foreach ($segments as $segment) {

            // If cron is enabled
            if ($segment->getEnabled() && trim($segment->getCronExpression()) != "") {

                // We must run this task if:
                // * time() is larger or equal to $nextrun
                //$run = @(time() >= $nextrun);
                if ($segment->getNextrun() <= $now) {

                    $this->output = new BufferedOutput();
                    $output->writeln(sprintf('Running Cron Task <info>%s</info>', $segment->getName()));

                    // Set $lastrun for this crontask
                    $segment->setLastrun(new \DateTime());
                    try {

                        //$commands = $crontask->getCommands();
                        // Execute request
                        $this->exportSegment($segment, $output);

                        $output->writeln('<info>SUCCESS EXPORT SEGMENT</info>');
                        $segment->setLog($this->output->fetch());
                        $segment->setStatus(1);

                    } catch (\Exception $e) {

                        $output->writeln('<error>ERROR EXPORT SEGMENT</error>');
                        $output->writeln('<error>' . $e->getMessage() . '</error>');
                        $output->writeln('<error>' . $e->getTraceAsString() . '</error>');
                        $segment->setStatus(2);
                        $segment->setLog($this->output->fetch() . "\r\n-----------------\r\n" . $e->getMessage() . "\r\n-----------------\r\n" . $e->getTraceAsString());

                    }

                    // Persist crontask
                    $em->persist($segment);
                    $em->flush();
                } else {
                    $output->writeln(sprintf('Skipping Segmentation Task <info>%s</info>', $segment->getName()));
                }

                // Get the last run time of this task, and calculate when it should run next
                $lastrun = $segment->getLastrun() ? $segment->getLastrun() : 0;
                $cron = CronExpression::factory($segment->getCronexpression());
                $nextrun = $cron->getNextRunDate($lastrun);
                if (!$segment->getNextrun() || $segment->getNextrun() <= $now) {
                    $segment->setNextrun($nextrun);
                    $em->persist($segment);
                }

            }

        }

        // Flush database changes
        $em->flush();

        $output->writeln('<comment>Done!</comment>');
    }

    private function exportSegment($segment, $output)
    {

        $result = "";
        $query = "";
        $fieldsToDisplayRaw = "";
        $fieldsToDisplay = array();
        $searchUtils = $this->getContainer()->get("search.utils");
        $exportUtils = $this->getContainer()->get('export_utils');
        $logger = $this->getContainer()->get('logger');

        if ($segment->getCode()) {

            $segmentation = $this->getContainer()->get('leadsfactory.mkgsegmentation_repository')->find($segment->getSegmentation());
            $savedSearch = $searchUtils->getKibanaSavedSearch($segmentation->getQueryCode());
            $query = $savedSearch->getQuery();

            SegmentUtils::addFilterConfig($query, $segment);
            $result = $searchUtils->request(ElasticSearchUtils::$PROTOCOL_POST, "/_search", $query);

            $fieldsToDisplayRaw = implode(";", $savedSearch->getColumns());
            $fieldsToDisplay = $savedSearch->getColumns();
        }

        if (!is_dir("datas/segments")) {
            mkdir("datas/segments");
        }

        $handle = fopen('datas/segments/segment-' . $segment->getId() . "-" . $segment->getCode() . ".csv", 'w');
        fputcsv($handle, $fieldsToDisplay, ";", "\"", "\\");
        $elements = $result->hits->hits;

        foreach ($elements as $row) {

            $leadsource = $row->_source;

            $content = array();
            foreach ($fieldsToDisplay as $fied) {

                try {
                    if (trim($fied) != "") {
                        if (strstr($fied, "content.")) {
                            $headerrow = str_replace("content.", "", $fied);
                            $obj = $leadsource->content;
                            $content[] = $obj->$headerrow;
                        } else {
                            $content[] = $leadsource->$fied;
                        }
                    }
                } catch (\Exception $e) {
                    $content[] = "";
                }

            }

            fputcsv($handle, $content, ";", "\"", "\\");

        }

        fclose($handle);

        if (trim($segment->getEmails()) != "") {

            $from = isset($params['from']) ? $params['from'] : $exportUtils::NOTIFICATION_DEFAULT_FROM;

            $emails = explode(";", $segment->getEmails());

            foreach ($emails as $email) {
                // Sending email
                $message = Swift_Message::newInstance()
                    ->setSubject($segment->getConfirmationemailssubjects())
                    ->setFrom($from)
                    ->setTo($email)
                    ->setBody($segment->getConfirmationEmailSource());


                $message->attach(
                    \Swift_Attachment::fromPath('datas/segments/segment-' . $segment->getId() . "-" . $segment->getCode() . ".csv")->setFilename('segment-' . $segment->getId() . "-" . $segment->getCode() . ".csv")
                );
                try {
                    $output->writeln("Sending mail for segment " . $segment->getName() . " to " . $email);
                    $output->writeln('<info>Sending mail for segment ' . $segment->getName() . ' to ' . $email . '</info>');
                    $result = $this->getContainer()->get('mailer')->send($message);
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    $logger->error($e->getMessage());
                }
            }

        }


    }

}
