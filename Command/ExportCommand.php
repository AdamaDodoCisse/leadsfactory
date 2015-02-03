<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends ContainerAwareCommand {
	

	
	protected function configure() {
		$this->setName('leadsfactory:export:leads')
		    ->setDescription('Export leads')
		    ->addArgument('form', InputArgument::OPTIONAL, 'form code')
		;
	}

    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('export.logger');
        $doctrine = $this->getContainer()->get('doctrine');

        $form = $input->getArgument('form');

        if($form){
            $output->writeln('Exporting '.$form.' leads...');
            $form = $this->getContainer()->get('leadsfactory.form_repository')->findByCode($form);
            $forms = array($form);
        }else{
            $output->writeln('Exporting all leads...');
            $forms = $this->getContainer()->get('leadsfactory.form_repository')->findAll();
        }

        foreach($forms as $form){
            $output->writeln($form->getName());
            try{
                $this->getContainer()->get('export_utils')->export($form);
                $output->writeln('Done');
            }catch(\Exception $e){
                $output->writeln('Error : '.$e->getMessage());
                $logger->error('Export '.$form->getName(). ' error : '.$e->getMessage());
            }
        }
	}



}