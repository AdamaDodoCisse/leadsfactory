<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 08:24
 */

namespace Tellaw\LeadsFactoryBundle\Shared;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CoreManager  implements ContainerAwareInterface {

    private static $v1 = 3.14;
    private static $v2 = 5;
    private static $v3 = 2;
    private static $v6 = 2.5;
    private static $v4 = 235;
    private static $v5 = 520;
    private static $v7 = 326;

    private $logger;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     *
     */
    public function setContainer(ContainerInterface $container = null) {

        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }


    public static function getLicenceInfos () {

        if (file_exists ( "../licence/licence.php" )) {

            $key = implode("",file ("../licence/licence.php"));
            
            $datas = explode( "|", $key );

            $dtf = ($datas[2] + CoreManager::$v2)/CoreManager::$v1;
            $plateform = $datas[3];
            $nbf = ( ($datas[4] * ( (CoreManager::$v2/CoreManager::$v3) ) ) / (CoreManager::$v7/2) );
            $nbs = ($datas[5] + CoreManager::$v5) / CoreManager::$v4 ;
            $stats = $datas[6];
            $nomClient = $datas[9];
            $socClient = $datas[7];
            $domains = $datas[8];

            $key = $datas[1];
            $calculation = md5($datas[2].":".$datas[3].":".$datas[4].":".$datas[5].":".$datas[6].":".$datas[8].":".$datas[7]);

            if ( $key != $calculation ) throw new \Exception ("Licence is not valid");

            $eol = new \DateTime();
            $eol->setTimestamp($dtf);

            $now = new \DateTime();

            if ($eol < $now) {
                throw new Exception ("Licence expirée");
            }

            return array (
                "isvalid" => true,
                "dtf" => $dtf,
                "plateform" => $plateform,
                "nbf" => $nbf,
                "nbs" => $nbs,
                "stats" => $stats,
                "nom" => $nomClient,
                "societe" => $socClient,
                "domains" => explode (',',$domains)
            );

        } else {

            throw new \Exception ("Licence file not found");

        }

    }

    public function isNewFormAccepted () {

        $infos = CoreManager::getLicenceInfos();
        $repo = $this->container->get('leadsfactory.form_repository');

        $nbForms = $repo->createQueryBuilder('name')
            ->select('COUNT(name)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($nbForms < $infos["nbf"]){
            return true;
        } else {
            return false;
        }


    }


    /**
     * Inverted
     * @return bool
     * @throws \Exception
     *
     */
    public function isNewScopeAccepted () {

        $infos = CoreManager::getLicenceInfos();
        $repo = $this->container->get('leadsfactory.scope_repository');

        $nbScopes = $repo->createQueryBuilder('name')
            ->select('COUNT(name)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($nbScopes < $infos["nbs"]){
            return false;
        } else {
            return true;
        }

    }

    public function isMonitoringAccepted () {

        $infos = CoreManager::getLicenceInfos();
        return $infos["stats"];

    }

    /**
     * @return int response inverted
     * @throws \Exception
     */
    public function isDomainAccepted () {

        $host = $_SERVER["HTTP_HOST"];
        $infos = CoreManager::getLicenceInfos();
        $domains = $infos["domains"];
        foreach ($domains as $domain) {
            if ( strstr ( $host, $domain ) ) {
                return 0;
            }
        }
        return 1;

    }

}