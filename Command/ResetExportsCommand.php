<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 27/10/15
 * Time: 16:55
 */

namespace Tellaw\LeadsFactoryBundle\Command;

use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Monolog\Logger;
use Swift_Message;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ResetExportsCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('leadsfactory:export:reset')
            ->setDescription('Review failed jobs and send email notification')
            ->addArgument('scope', InputArgument::REQUIRED, 'Specify a scope code (ti, weka, comundi ... or all)')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $scope = $input->getArgument('scope');

        if ($scope == 'all') {
            $sr = $this->getContainer()->get('leadsfactory.scope_repository');
            $scope_list = $sr->getAll();
            foreach ($scope_list as $s) {
                $this->callExportForScope($s['s_code']);
            }
        } else {
            $this->callExportForScope($scope);
        }
    }

    private function callExportForScope($scope) {
        $logger = $this->getContainer()->get('export.logger');
        $date = new \DateTime('now');
        $date->modify('-30 days');
        $er = $this->getContainer()->get('leadsfactory.export_repository');
        $prefs = $this->getContainer()->get('leadsfactory.preference_repository');
        $e_ids = array();

        $logger->info("REVIEW JOBS : ".$scope." tâche de recupération des exports en erreur multiple");
        $exports = $er->findByStatus(3, $date);
        // If We have failed exports
        $logger->info("REVIEW JOBS : ".$scope." Nombre total d'exports en erreur : " . count($e_ids));
        if (is_array($exports) && count($exports) > 0) {
            $export_email = $prefs->findByKeyAndScope('CORE_EXPORT_EMAIL', intval($exports[0]['s_id']));

            // Get Exports Id List in this scope
            foreach ($exports as $e) {
                if ($scope && $scope != $e['s_code']) continue ; // If there is a scope get or Go !
                $e_ids[] = $e['e_id'];
            }

            $logger->info("REVIEW JOBS : ".$scope." : Nombre d'exports en erreur : " . count($e_ids));
            // Update exports and format mail
            if (count($e_ids)) {
                $logger->info("REVIEW JOBS : ".$scope." : Liste des exports : ".implode(', ', $e_ids));
                $logger->info("REVIEW JOBS : ".$scope." : Remise en attente des exports");
                // Reset des exports
                $er->resetFailedExports($date);
                $message = $this->formatExportTable($exports, $scope);
                if (is_array($export_email) && count($export_email) && $export_email[0]['p_value']) {
                    $email = explode(';', $export_email[0]['p_value']);
                    $this->sendExportLogsMail($message, $email , $scope);
                } else {
                    $logger->info("REVIEW JOBS : Email d'export introuvalble");
                }
            }
        }
    }

    /**
     * @param $data
     * @param $scope
     * @return string
     */
    private function formatExportTable($data, $scope) {

        $t = '<table style="width:100%"><tr>'
            .'<td>ID_EXPORT</td>'
            .'<td>ID_LEAD</td>'
            .'<td>ID_FORM</td>'
            .'<td>NOM DU FORM.</td>'
            .'<td>DATE DE CREATION</td>'
            .'<td>EXECUTE LE</td>'
            .'<td>METHODE</td>'
            .'<td>LOG</td></tr>';
        foreach ($data as $e) {
            if ($scope && $scope != $e['s_code']) continue ; // If there is a scope get or Go !
            $executed = ($e['e_executed_at'] ? date_format($e['e_executed_at'],"Y/m/d H:i:s") : 0);
            $t .= '<tr>';
            $t .= '<td>'. $e['e_id'] .'</td>';
            $t .= '<td>'. $e['l_id'] .'</td>';
            $t .= '<td>'. $e['f_id'] .'</td>';
            $t .= '<td>'. $e['f_name'] .'</td>';
            $t .= '<td>'. date_format($e['e_created_at'],"Y/m/d H:i:s") .'</td>';
            $t .= '<td>'. $executed .'</td>';
            $t .= '<td>'. $e['e_method'] .'</td>';
            $t .= '<td>'. $e['e_log'] .'</td>';
            $t .= '</tr>';
        }
        $t .= '</table>';
        return ($t);
    }

    /**
     * @param $table
     * @param $export_email
     * @param $scope
     */
    private function sendExportLogsMail($table, $export_email, $scope) {

        $logger = $this->getContainer()->get('export.logger');
        $exportUtils = $this->getContainer()->get('export_utils');

        $title = "[LEADS Factory] Revue des exports en échec : ".$scope;
        $from = $exportUtils::NOTIFICATION_DEFAULT_FROM;
        $body = '<html>Bonjour,<br><br>'
                .'Ci dessous la liste des exports en echec sur les 30 derniers jours.<br><br>'
                .$table
                .'<br><br>Ils sont remis en attente de traitement'
                .'<br><br><br><em>Message automatique.</em></html>';

        foreach($export_email as $email) {

            $message = Swift_Message::newInstance()
                ->setSubject($title)
                ->setFrom($from)
                ->setTo($email)
                ->setBody($body)
                ->setContentType("text/html");

            $logger->info("REVIEW JOBS : ".$scope." : Envoie du mail à : " . $email);
            try {
                $this->getContainer()->get('mailer')->send($message);
            } catch(Exception $e){
                $logger->error($e->getMessage());
            }
        }

    }
}