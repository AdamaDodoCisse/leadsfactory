<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SegmentationCommand extends ContainerAwareCommand {
	

	
	protected function configure() {
		$this->setName('leadsfactory:export:segmentation')
		    ->setDescription('Segmentation : Export configured segments')
		;
	}

    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('export.logger');
        $doctrine = $this->getContainer()->get('doctrine');

        $output->writeln('Exporting all segments...');
        $forms = $this->getContainer()->get('leadsfactory.form_repository')->findAll();

        foreach($forms as $form){
            $output->writeln($form->getName());
            try{
                $this->getContainer()->get('export_utils')->export($form);
                $output->writeln('Done');
            }catch(\Exception $e){
                $output->writeln('Error : '.$e->getMessage());
	            $output->writeln('Error : '.$e->getTraceAsString());
	            $logger->error('Export '.$form->getName(). ' error : '.$e->getMessage());
	            $logger->error($e->getTraceAsString());
            }
        }
	}



}