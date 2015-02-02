<?php 

namespace Tellaw\LeadsFactoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CronRunnerCommand extends ContainerAwareCommand {
	
	private $cronjobs = array();
	
	protected function configure() {
		$this
		->setName('leadsfactory:cronjobs:alertsByMail')
		->setDescription('Cron Job ALERT LeadsFactorty')
		->addArgument('mode', InputArgument::OPTIONAL, 'set to true to force cronjob execution')
		;
	}

    protected function execute(InputInterface $input, OutputInterface $output) {

        $dayToTest = new \DateTime();
        $dayToTest->sub(new \DateInterval('P1D'));

        $dayForPeriodBefore = new \DateTime();
        $dayToTest->sub(new \DateInterval('P9D'));

		// First Iterate over Scopes
        $scopes = $this->getContainer()->get("doctrine")->getManager()->getRepository('TellawLeadsFactoryBundle:Scope')->findAll();

        foreach ( $scopes as $scope ) {

            //$currentScope = return $this->get('security.context')->getToken()->getUser()->getScope()->getId();

            $typesInError = array();
            $typesInWarning = array();

            // Second iterate over Types
            $types = $this->getContainer()->get('leadsfactory.form_type_repository')->findByScope($scope->getId());
            foreach ( $types as $type ) {

                $yesterdayLeads = $this->getTypeLeadsForDay( $dayToTest, $type->getId() );
                echo ("Number of leads : ".$yesterdayLeads);

            }

            $formsInError = array();
            $formsInWarning = array();

            // Third, iterate over forms
            $forms = $this->get('leadsfactory.form_repository')->findByScope($scope->getId());
            foreach ( $forms as $form ) {

                

            }

        }

        // Done


	}

    private function getTypeLeadsForDay ( $date, $type ) {

        $query = $this->getContainer()->get("doctrine")->getManager()->getConnection()->prepare('SELECT count(1) as count FROM Leads WHERE form_type_id = :formType AND createdAt = :minDate GROUP BY DAY(createdAt)');
        $query->bindValue('minDate', $date);
        $query->bindValue('formType', $type);
        $query->execute();
        $results = $query->fetchAll();

        return $query->getResult();

    }

}