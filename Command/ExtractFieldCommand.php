<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 19/01/15
 */

namespace Tellaw\LeadsFactoryBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\LeadsRepository;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\Question;

class ExtractFieldCommand extends Command
{
	/** @var  EntityManager */
	private $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		parent::__construct();
	}

	protected function configure()
	{
		$this->setName('leadsfactory:extract-field')
		     ->setDescription('Extract a field to its own column')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var QuestionHelper $question_helper */
		$question_helper = $this->getHelper('question');

		$field_name = $question_helper->ask($input, $output,
			new Question("Quel champ voulez-vous extraire ?\n(Les leads ne contenant pas ce champ ne seront pas modifiées) : ")
		);

		$attribute_name = $question_helper->ask($input, $output,
			new Question("Vers quel attribut de l'entité ? (Celui-ci doit déjà exister) : ")
		);

		$setter = 'set'.ucfirst($attribute_name);
		if ( ! method_exists('Tellaw\LeadsFactoryBundle\Entity\Leads', $setter)) {
			$output->writeln('La méthode Tellaw\LeadsFactoryBundle\Entity\Leads::'.$setter.' n\'existe pas!');
			return;
		}

		$leads_repository = $this->em->getRepository('TellawLeadsFactoryBundle:Leads');
		$leads = $leads_repository->findAll();

		$progress = new ProgressBar($output, count($leads));
		$progress->setRedrawFrequency(50);

		$count = 0;
		/** @var Leads $lead */
		foreach ($leads as $lead) {
			$progress->advance();
			$data = $lead->getData();
			if (empty($data)) {
				continue;
			}
			try {
				$data = json_decode($data, true);
			} catch (Exception $e) {
				$output->writeln($e->getMessage());
				continue;
			}

			if (array_key_exists($field_name, $data)) {
				++$count;
				$lead->{$setter}($data[$field_name]);
			}
		}
		$progress->finish();

		$this->em->flush();

		$output->writeln('');
		$output->writeln("$count $attribute_name extraits");
	}
}
