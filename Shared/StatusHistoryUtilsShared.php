<?php
namespace Tellaw\LeadsFactoryBundle\Shared; use Symfony\Component\DependencyInjection\ContainerAwareInterface; class StatusHistoryUtilsShared implements ContainerAwareInterface { protected $container; public function __construct() { } public function setContainer(ContainerInterface $sp8cfdd2 = null) { $this->container = $sp8cfdd2; } protected function getContainer() { return $this->container; } public function getCurrentStatusForMonitor($spc08b20) { } public function getActualStatusDurationForMonitor($spc08b20) { } }