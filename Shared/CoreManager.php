<?php
namespace Tellaw\LeadsFactoryBundle\Shared; use Symfony\Component\DependencyInjection\ContainerAwareInterface; use Symfony\Component\DependencyInjection\ContainerInterface; class CoreManager implements ContainerAwareInterface { private static $v1 = 3.14; private static $v2 = 5; private static $v3 = 2; private static $v6 = 2.5; private static $v4 = 235; private static $v5 = 520; private static $v7 = 326; private $logger; protected $container; public function setContainer(ContainerInterface $sp8cfdd2 = null) { $this->container = $sp8cfdd2; $this->logger = $this->container->get('logger'); } protected function getContainer() { return $this->container; } public static function getLicenceInfos() { if (file_exists('../licence/licence.php')) { $sp89c202 = implode('', file('../licence/licence.php')); $sp5ea0e9 = explode('|', $sp89c202); $spadc481 = ($sp5ea0e9[2] + CoreManager::$v2) / CoreManager::$v1; $sp2c2b4c = $sp5ea0e9[3]; $spcc1c48 = $sp5ea0e9[4] * (CoreManager::$v2 + 0.5) / (CoreManager::$v7 / 2); $sp448133 = ($sp5ea0e9[5] + CoreManager::$v5) / CoreManager::$v4; $spdc367b = $sp5ea0e9[6]; $sp25ed12 = $sp5ea0e9[9]; $sp6266cc = $sp5ea0e9[7]; $sp3feb1e = $sp5ea0e9[8]; $sp89c202 = $sp5ea0e9[1]; $sp66649a = md5($sp5ea0e9[2] . ':' . $sp5ea0e9[3] . ':' . $sp5ea0e9[4] . ':' . $sp5ea0e9[5] . ':' . $sp5ea0e9[6] . ':' . $sp5ea0e9[8] . ':' . $sp5ea0e9[7]); if ($sp89c202 != $sp66649a) { throw new \Exception('Licence is not valid'); } $sp4be3e3 = new \DateTime(); $sp4be3e3->setTimestamp($spadc481); $sp33b9ca = new \DateTime(); if ($sp4be3e3 < $sp33b9ca) { throw new Exception('Licence expirée'); } return array('isvalid' => true, 'dtf' => $spadc481, 'plateform' => $sp2c2b4c, 'nbf' => $spcc1c48, 'nbs' => $sp448133, 'stats' => $spdc367b, 'nom' => $sp25ed12, 'societe' => $sp6266cc, 'domains' => explode(',', $sp3feb1e)); } else { throw new \Exception('Licence file not found'); } } public function isNewFormAccepted() { $sp628891 = CoreManager::getLicenceInfos(); $spa4634e = $this->container->get('leadsfactory.form_repository'); $spd7f831 = $spa4634e->createQueryBuilder('name')->select('COUNT(name)')->getQuery()->getSingleScalarResult(); if ($spd7f831 < $sp628891['nbf']) { return true; } else { return false; } } public function isNewScopeAccepted() { $sp628891 = CoreManager::getLicenceInfos(); $spa4634e = $this->container->get('leadsfactory.scope_repository'); $sp4d36a3 = $spa4634e->createQueryBuilder('name')->select('COUNT(name)')->getQuery()->getSingleScalarResult(); if ($sp4d36a3 < $sp628891['nbs']) { return false; } else { return true; } } public function isMonitoringAccepted() { $sp628891 = CoreManager::getLicenceInfos(); return $sp628891['stats']; } public function isDomainAccepted() { $spaa83e2 = $_SERVER['HTTP_HOST']; $sp628891 = CoreManager::getLicenceInfos(); $sp3feb1e = implode(',', $sp628891['domains']); if (strstr($spaa83e2, $sp3feb1e)) { $this->logger->info('domain accepted'); return 0; } else { $this->logger->info('domain refused : Accepted : ' . $sp3feb1e); return 1; } } }