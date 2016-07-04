<?php 

namespace Weka\LeadsExportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tellaw\LeadsFactoryBundle\Entity\Users;
use Tellaw\LeadsFactoryBundle\Entity\StatisticPoolElement;

class ComundiStatisticsCommand extends ContainerAwareCommand {
	
    private static $_STATISTIC_CODE = "comundi_tx_by_user";
	
	protected function configure() {
		$this->setName('leadsfactory:comundi:statistics')
		    ->setDescription('Generate daily stats for Comundi')
            ->addArgument('scopeId', InputArgument::OPTIONAL, 'Scope ID')
		;
	}

    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('export.logger');
        $searchUtils = $this->getContainer()->get('search.utils');

        $this->output = new BufferedOutput();

        // Calcul du taux de transformation par utilisateur Comundi

        // load Scope comundi
        $scopeId = $input->getArgument('scopeId');

        if (trim($scopeId) == "") throw new \Exception ("Scope cannot be empty");

        /**
         * LOADING TX INFORMATIONS FOR USERS
         */

        $scope = $this->getContainer()->get("doctrine")->getRepository("TellawLeadsFactoryBundle:Scope")->find( $scopeId );

        // 1) Faire une boucle sur l'ensemble des utilisateurs du scope Comundi
        $users = $this->getContainer()->get("doctrine")->getRepository("TellawLeadsFactoryBundle:Users")->findByScope( $scopeId );

        //$output->writeln(sprintf('Users loaded : <info>%i</info>', $users->count() ));

        // 2) Pour chacun calculer : 100* Nb gagné / ( Nb total - Nb en attente )
        foreach ($users as $user) {

            $output->writeln(sprintf('User name : <info>%s</info>', $user->getLastName() . " - ".$user->getId() ));
            $obj = $this->calculateTxRateForUser( array($user) );

            $statisticObject = $this->saveStatisticValue( "user-tx-".$user->getId(), ucfirst($user->getLastName()). " ".ucfirst($user->getFirstName()), $obj["txRate"] );
            $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );

            $statisticObject = $this->saveStatisticValue( "user-gagne-".$user->getId(), ucfirst($user->getLastName()). " ".ucfirst($user->getFirstName()), $obj["nbGagne"] );
            $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );

            $statisticObject = $this->saveStatisticValue( "user-perdu-".$user->getId(), ucfirst($user->getLastName()). " ".ucfirst($user->getFirstName()), $obj["nbPerdu"] );
            $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );

            $total = $this->getNumberOfLeadsForUser( array($users) );
            $statisticObject = $this->saveStatisticValue( "user-total-".$user->getId(), ucfirst($user->getLastName()). " ".ucfirst($user->getFirstName()), $total );
            $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );

        }

        /**
         * LOADING TX INFORMATIONS FOR BU
         */

        $jsonArray = null;
        $json = null;
        $filePath = $this->getContainer()->get('kernel')->getRootDir()."/config/".$scope->getCode()."-team-description.json";
        if (file_exists( $filePath )) {
            $jsonArray = json_decode(file_get_contents( $filePath ), true);
        }


        if ( $jsonArray ) {

            foreach ( $jsonArray as $manager ) {

                foreach ($manager as $team) {

                    $members = $team["members"];
                    $teamId = $team["name"];
                    $teamName = $team["id"];

                    $output->writeln(sprintf('Team name : <info>%s</info>', $teamName ));

                    $users = $this->getContainer()->get("doctrine")->getRepository("TellawLeadsFactoryBundle:Users")->findBy( array("email" => $members) );
                    $obj = $this->calculateTxRateForUser( $users );

                    $statisticObject = $this->saveStatisticValue( "team-tx-".$teamId, $teamName, $obj["txRate"] );
                    $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );

                    $statisticObject = $this->saveStatisticValue( "team-gagne-".$teamId, $teamName, $obj["nbGagne"] );
                    $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );

                    $statisticObject = $this->saveStatisticValue( "team-perdu-".$teamId, $teamName, $obj["nbPerdu"] );
                    $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );

                    // Save Number of leads for Team
                    $total = $this->getNumberOfLeadsForUser( $users );
                    $statisticObject = $this->saveStatisticValue( "team-total-".$teamId, $teamName, $total );
                    $searchUtils->indexStatisticObject ( $statisticObject->asArray(), $scope->getCode() );
                }

            }

        }

	}

    private function saveStatisticValue ( $name, $label, $value ) {

        // Look if there is not already some datas for the day in the DB, update then, if not insert
        $statisticValue = $this->getContainer()->get("doctrine")
            ->getManager()
            ->createQuery('SELECT e FROM TellawLeadsFactoryBundle:StatisticPoolElement e WHERE e.name = :userId AND e.created_at <= :todayLate AND e.created_at >= :todayEarly')
            ->setParameter (":userId", $name )
            ->setParameter (":todayLate", \DateTime::createFromFormat('j-M-Y H:i:s', date("j-M-Y")." 23:59:59")  )
            ->setParameter (":todayEarly", \DateTime::createFromFormat('j-M-Y H:i:s', date("j-M-Y")." 00:00:00"))
            ->getResult();

        // 3) Stocker l'information
        if ( count( $statisticValue ) == 0) {
            $statisticValue = new StatisticPoolElement();
        } else {
            $statisticValue = $statisticValue[0];
        }

        $statisticValue->setCode( ComundiStatisticsCommand::$_STATISTIC_CODE );
        $statisticValue->setName ( $name );
        $statisticValue->setLabel ( ucfirst($label) );
        $statisticValue->setValue( $value );
        $statisticValue->setCreatedAt( new \DateTime());

        $em = $this->getContainer()->get("doctrine")->getManager();
        $em->persist($statisticValue);
        $em->flush();

        return $statisticValue;

    }

    private function calculateTxRateForUser ( $users ) {

        /**
         * Le calcul du taux de transformation est :
         *
         * 100 * ( NB_GAGNE / ( NB_GAGNE + NB_PERDU ) )
         *
         */

        $gagnes = $this->getContainer()->get("doctrine")
            ->getManager()
            ->createQuery('SELECT e FROM TellawLeadsFactoryBundle:Leads e WHERE e.user IN (:userId) AND e.workflowStatus = :workflowStatus AND e.createdAt >= :beginDate')
            ->setParameter (":userId",$users )
            ->setParameter (":workflowStatus", "gagne"  )
            ->setParameter (":beginDate", \DateTime::createFromFormat('j-M-Y', '1-1-2016'))
            ->getResult();
        $nbGagne = count($gagnes);

        echo ("Leads : Gagné => ".$nbGagne."\n");

        $perdu = $this->getContainer()->get("doctrine")
            ->getManager()
            ->createQuery('SELECT e FROM TellawLeadsFactoryBundle:Leads e WHERE e.user IN (:userId) AND e.workflowStatus = :workflowStatus AND e.createdAt >= :beginDate')
            ->setParameter (":userId",$users )
            ->setParameter (":workflowStatus", "perdu"  )
            ->setParameter (":beginDate", \DateTime::createFromFormat('j-M-Y', '1-1-2016'))
            ->getResult();
        $nbPerdu = count($perdu);

        echo ("Leads : Perdu => ".$nbPerdu."\n");

        if ( $nbGagne +  $nbPerdu > 0 ) {
            $tx = (100*$nbGagne) / ($nbGagne +  $nbPerdu);
        } else {
            $tx = 0;
        }

        echo ("Leads : TX => ".$tx."\n");

        return array( "txRate" => (string)$tx, "nbGagne" => (string)$nbGagne, "nbPerdu" => $nbPerdu );

    }

    private function getNumberOfLeadsForUser ( $users ) {

        $total = $this->getContainer()->get("doctrine")
            ->getManager()
            ->createQuery('SELECT e FROM TellawLeadsFactoryBundle:Leads e WHERE e.user IN (:userId) AND e.createdAt >= :beginDate')
            ->setParameter (":userId",$users )
            ->setParameter (":beginDate", \DateTime::createFromFormat('j-M-Y', '1-1-2016'))
            ->getResult();
        $nbTotal = count($total);

        return $nbTotal;

    }




}