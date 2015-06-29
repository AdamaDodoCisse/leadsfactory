<?php
namespace Tellaw\LeadsFactoryBundle\Shared; use Symfony\Component\DependencyInjection\ContainerAwareInterface; use Symfony\Component\DependencyInjection\ContainerInterface; class ExportUtilsShared implements ContainerAwareInterface { protected $container; public function setContainer(ContainerInterface $sp8cfdd2 = null) { $this->container = $sp8cfdd2; } public function createJob($sp22badd) { $sp6974e7 = $this->getContainer()->get('export.logger'); $spc5a69c = $sp22badd->getForm()->getConfig(); foreach ($spc5a69c['export'] as $sp0d0d0a => $sp7ffea5) { $spf73837 = new Export(); if (!$this->isValidExportMethod($sp0d0d0a)) { $spf73837->setLog('Méthode d\'export invalide'); $sp6974e7->info('Méthode d\'export invalide (formulaire ID ' . $sp22badd->getForm()->getId() . ')'); } $spf73837->setMethod($sp0d0d0a); $spf73837->setLead($sp22badd); $spf73837->setForm($sp22badd->getForm()); $sp9f02aa = $this->getInitialExportStatus($sp22badd, array('method' => $sp0d0d0a, 'method_config' => $sp7ffea5)); $spf73837->setStatus($sp9f02aa); $spf73837->setCreatedAt(new \DateTime()); $spf73837->setScheduledAt($this->getScheduledDate($sp7ffea5)); try { $spc9900d = $this->getContainer()->get('doctrine')->getManager(); $spc9900d->persist($spf73837); $spc9900d->flush(); $sp6974e7->info('Job export (ID ' . $spf73837->getId() . ') créé avec succès'); } catch (Exception $sp516ea4) { $sp6974e7->error($sp516ea4->getMessage()); } } } protected function getInitialExportStatus($sp22badd, $spc5a69c) { $spedf936 = $spc5a69c['method_config']; $sp69d110 = $this->getMethod($spc5a69c['method']); if (array_key_exists('if_email_validated', $spedf936) && $spedf936['if_email_validated'] === true) { $spa9a8c7 = $sp22badd->getEmail(); $sp353cef = $sp69d110->isEmailValidated($sp22badd, $spa9a8c7); if ($sp353cef) { return self::$_EXPORT_NOT_PROCESSED; } else { return self::EXPORT_EMAIL_NOT_CONFIRMED; } } else { return self::$_EXPORT_NOT_PROCESSED; } } protected function getScheduledDate($sp7ffea5) { $spe7c07e = isset($sp7ffea5['cron']) ? $sp7ffea5['cron'] : $this->_defaultCronExp; $sp8bec09 = CronExpression::factory($spe7c07e); return $sp8bec09->getNextRunDate($this->getMinDate($sp7ffea5)); } protected function getMinDate($sp7ffea5) { if (!isset($sp7ffea5['gap']) || trim($sp7ffea5['gap']) == '') { return 'now'; } $spdfd14e = new \DateTime(); return $spdfd14e->add(new \DateInterval('PT' . trim($sp7ffea5['gap']) . 'M')); } public function export($sp450a62) { $sp6974e7 = $this->getContainer()->get('export.logger'); $spc5a69c = $sp450a62->getConfig(); if (!isset($spc5a69c['export'])) { return; } foreach ($spc5a69c['export'] as $sp0d0d0a => $sp7ffea5) { if (!$this->isValidExportMethod($sp0d0d0a)) { $sp6974e7->error('Méthode d\'export "' . $sp0d0d0a . '" invalide'); continue; } $sp0c5340 = $this->getExportableJobs($sp450a62, $sp0d0d0a, $sp7ffea5); if (count($sp0c5340)) { $this->getMethod($sp0d0d0a)->export($sp0c5340, $sp450a62); } } } protected function getExportableJobs($sp450a62, $sp0d0d0a, $sp7ffea5) { $spc9900d = $this->getContainer()->get('doctrine')->getManager(); $sp49f6e6 = $spc9900d->createQuery('SELECT j
            FROM TellawLeadsFactoryBundle:Export j
            WHERE j.form = :form
              AND j.method = :method
              AND j.scheduled_at <= :now
              AND j.status NOT IN (:status)'); $sp49f6e6->setParameters(array('form' => $sp450a62, 'method' => $sp0d0d0a, 'now' => new \DateTime(), 'status' => array(self::$_EXPORT_SUCCESS, self::EXPORT_EMAIL_NOT_CONFIRMED, self::$_EXPORT_NOT_SCHEDULED))); return $sp49f6e6->getResult(); } public function updateJob($spf73837, $sp9f02aa, $sp368219 = '') { $spf73837->setStatus($sp9f02aa); $spf73837->setExecutedAt(new \DateTime()); $spf73837->setLog($sp368219); try { $spc9900d = $this->getContainer()->get('doctrine')->getManager(); $spc9900d->persist($spf73837); $spc9900d->flush(); } catch (\Exception $sp516ea4) { $this->getContainer()->get('export.logger')->error($sp516ea4->getMessage()); } } public function updateLead($sp22badd, $sp9f02aa, $sp368219, $sp7b1918 = null) { $sp7b1918 = is_null($sp7b1918) ? new \DateTime() : $sp7b1918; $sp22badd->setStatus($sp9f02aa); $sp22badd->setLog($sp368219); $sp22badd->setExportdate($sp7b1918); try { $spc9900d = $this->getContainer()->get('doctrine')->getManager(); $spc9900d->persist($sp22badd); $spc9900d->flush(); } catch (\Exception $sp516ea4) { $this->getContainer()->get('export.logger')->error($sp516ea4->getMessage()); } } public function getErrorStatus($spf73837) { if ($spf73837->getStatus() == self::$_EXPORT_NOT_PROCESSED || is_null($spf73837->getStatus())) { return self::$_EXPORT_ONE_TRY_ERROR; } else { return self::$_EXPORT_MULTIPLE_ERROR; } } }