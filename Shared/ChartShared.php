<?php
namespace Tellaw\LeadsFactoryBundle\Shared; use Tellaw\LeadsFactoryBundle\Utils\Chart; class ChartShared { public function getSpecialGraphIndexes() { return $this->specialGraphIndexes; } public function getGraphCount() { return $this->graph_count; } public function setPeriod($sp3a49a1) { $this->period = $sp3a49a1; } public function getPeriod() { return $this->period; } public function setFormType($sp4246c4) { if (empty($sp4246c4)) { $this->formType = $this->_getAllFormTypes(); } else { $this->formType = empty($sp4246c4) || is_array($sp4246c4) ? $sp4246c4 : array($sp4246c4); } } public function getFormType() { return $this->formType; } public function setForm($spa09441) { $this->form = $spa09441; } public function getForm() { return $this->form; } protected function _loadLeadsDataByTypes() { $spfab6f9 = $this->container->get('doctrine')->getManager(); $spf4cdf1 = array(); foreach ($this->formType as $sp4246c4) { if (!is_object($sp4246c4)) { $sp4246c4 = $this->getContainer()->get('leadsfactory.form_type_repository')->findOneById($sp4246c4); } $sp499efd = $spfab6f9->createQueryBuilder(); $sp499efd->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))->from('TellawLeadsFactoryBundle:Leads', 'l')->where('l.formType = :form_type_id')->andWhere('l.createdAt >= :minDate')->andWhere('l.createdAt <= :maxDate')->groupBy($this->_getSqlGroupByClause())->setParameter('format', $this->_getSqlDateFormat())->setParameter('form_type_id', $sp4246c4->getId())->setParameter('minDate', $this->_getRangeMinDate()->format('Y-m-d'))->setParameter('maxDate', $this->_getRangeMaxDate()->format('Y-m-d')); $sp499efd = $this->excludeInternalLeads($sp499efd); $sp401513 = $sp499efd->getQuery()->getResult(); array_unshift($sp401513, $sp4246c4->getName()); $spf4cdf1[$sp4246c4->getName()] = $sp401513; } return $spf4cdf1; } protected function _loadLeadsDataByFormsType() { $sp5462e3 = $this->_getRangeMinDate()->format('Y-m-d H:i:s'); $spfab6f9 = $this->container->get('doctrine')->getManager(); $spf4cdf1 = array(); $sp0028a8 = $this->formType[0]; $spdeaad5 = $this->container->get('leadsfactory.form_repository')->findByFormType($sp0028a8); foreach ($spdeaad5 as $spa09441) { $sp499efd = $spfab6f9->createQueryBuilder(); $sp499efd->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))->from('TellawLeadsFactoryBundle:Leads', 'l')->where('l.form = :form_id')->andWhere('l.createdAt >= :minDate')->groupBy($this->_getSqlGroupByClause())->setParameter('format', $this->_getSqlDateFormat())->setParameter('form_id', $spa09441->getId())->setParameter('minDate', $sp5462e3); $sp499efd = $this->excludeInternalLeads($sp499efd); $sp401513 = $sp499efd->getQuery()->getResult(); array_unshift($sp401513, $spa09441->getName()); $spf4cdf1[$spa09441->getId()] = $sp401513; } return $spf4cdf1; } protected function _loadLeadsDataByForm() { $sp5462e3 = $this->_getRangeMinDate()->format('Y-m-d H:i:s'); $spfab6f9 = $this->container->get('doctrine')->getManager(); $spf4cdf1 = array(); foreach ($this->form as $spa09441) { if (!$spa09441 instanceof Form) { $spa09441 = $this->container->get('leadsfactory.form_repository')->findOneById($spa09441); } if ($spa09441 === null) { continue; } $sp499efd = $spfab6f9->createQueryBuilder(); $sp499efd->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))->from('TellawLeadsFactoryBundle:Leads', 'l')->where('l.form = :form_id')->andWhere('l.createdAt >= :minDate')->andWhere('l.createdAt <= :maxDate')->groupBy($this->_getSqlGroupByClause())->setParameter('format', $this->_getSqlDateFormat())->setParameter('form_id', $spa09441->getId())->setParameter('minDate', $this->_getRangeMinDate()->format('Y-m-d'))->setParameter('maxDate', $this->_getRangeMaxDate()->format('Y-m-d')); $sp499efd = $this->excludeInternalLeads($sp499efd); $sp401513 = $sp499efd->getQuery()->getResult(); array_unshift($sp401513, $spa09441->getName()); $spf4cdf1[$spa09441->getName()] = $sp401513; } return $spf4cdf1; } protected function _getRangeMinDate() { $sp8e4999 = $this->container->get('lf.utils'); $sp2b8ebc = $sp8e4999->getUserPreferences(); return clone $sp2b8ebc->getDataPeriodMinDate(); } protected function _getRangeMaxDate() { $sp8e4999 = $this->container->get('lf.utils'); $sp2b8ebc = $sp8e4999->getUserPreferences(); return $sp2b8ebc->getDataPeriodMaxDate(); } protected function _getAllFormTypes() { $spfab6f9 = $this->container->get('doctrine')->getManager(); $spebccd6 = $this->getContainer()->get('leadsfactory.form_type_repository')->findAll(); return $spebccd6; } protected function _formatChartData($spf4cdf1) { $sp5462e3 = $this->_getRangeMinDate(); $spd9afb4 = $this->_getRangeMaxDate(); $sp03715e = $sp5462e3->diff($spd9afb4); $sp43b8d3 = $sp03715e->format('%R%a'); $spb0106a = $sp03715e->format('%R%m'); if ($sp43b8d3 < Chart::ZOOM_SWITCH_RANGE) { $sp2ba483 = true; } else { $sp2ba483 = false; } $spa1a375 = array(); $sp722d88 = array(); $sp5b3199 = true; foreach ($spf4cdf1 as $sp26fae0) { $spb72f6c = array_shift($sp26fae0); $spa1a375[$spb72f6c] = array(); $spff53b5 = $this->_getRangeMinDate(); if ($sp2ba483) { for ($sp21a39f = 0; $sp21a39f <= $sp43b8d3; $sp21a39f++) { if ($sp5b3199) { $sp722d88[] = (string) $spff53b5->format('d/m/Y'); } $spa1a375[$spb72f6c][(string) $spff53b5->format('d/m/Y')] = 0; $spff53b5->add(new \DateInterval('P1D')); } $sp5b3199 = false; foreach ($sp26fae0 as $sp0ff49e) { $spa1a375[$spb72f6c][(string) $sp0ff49e['date']] = $sp0ff49e['n']; } } else { for ($sp21a39f = 0; $sp21a39f < $spb0106a; $sp21a39f++) { die; } } } $this->graphTimeRange = $sp722d88; $sp43ec36 = array(); $sp36568c = 0; foreach ($spa1a375 as $spbf1f67 => $sp26fae0) { $sp43ec36[$sp36568c] = array(); $sp43ec36[$sp36568c][] = $spbf1f67; foreach ($sp26fae0 as $sp90123a) { $sp43ec36[$sp36568c][] = (int) $sp90123a; } $sp36568c++; } if (Chart::DEBUG_MODE) { var_dump('Formated output'); var_dump($spa1a375); var_dump('Google Formated output <chartData>'); var_dump($sp43ec36); var_dump('TimeRange output'); var_dump($sp722d88); } return $sp43ec36; } protected function _addAdditionalGraphs($spf06675) { $this->graph_count = count($spf06675); $sp8e4999 = $this->container->get('lf.utils'); $sp326b1c = $sp8e4999->getUserPreferences(); if ($sp326b1c->getDataDisplayAverage()) { $spf06675[] = $this->_addAverageGraph($spf06675); } if ($sp326b1c->getDataDisplayTotal()) { $spf06675[] = $this->_addTotalGraph($spf06675); } $this->setSpecialGraphIndexes($spf06675); $this->setNormalGraph($spf06675); return $spf06675; } protected function _addTotalGraph($spf06675) { $sp6edf5b = count($spf06675[0]); $spcfd305 = array('Total'); for ($sp96ec35 = 1; $sp96ec35 < $sp6edf5b; $sp96ec35++) { $sp907eb4 = 0; for ($sp060a6e = 0; $sp060a6e < $this->graph_count; $sp060a6e++) { $sp907eb4 += $spf06675[$sp060a6e][$sp96ec35]; } $spcfd305[$sp96ec35] = $sp907eb4; } return $spcfd305; } protected function _addAverageGraph($spf06675) { $sp6edf5b = count($spf06675[0]); $spcf97bf = count($spf06675); $sp907eb4 = 0; $sp49f59a = array('Moyenne'); for ($sp96ec35 = 1; $sp96ec35 < $sp6edf5b; $sp96ec35++) { $sp907eb4 = 0; foreach ($spf06675 as $spcbb237) { $sp907eb4 += $spcbb237[$sp96ec35]; } $sp49f59a[] = $sp907eb4 / $spcf97bf; } return $sp49f59a; } public function getTimeRange() { return json_encode($this->graphTimeRange); } protected function _getYearRange() { $spbc21c3 = new \DateTime(); $spe2a8ab = $this->_getRangeMinDate(); $sp3e45c1 = array(); while ($spe2a8ab <= $spbc21c3) { $sp3e45c1[] = $spe2a8ab->format('M y'); $spe2a8ab->modify('+1 month'); } return $sp3e45c1; } protected function _getMonthRange() { $spbc21c3 = new \DateTime(); $spe2a8ab = $this->_getRangeMinDate(); $sp3e45c1 = array(); while ($spe2a8ab <= $spbc21c3) { $sp3e45c1[] = $spe2a8ab->format('d/m'); $spe2a8ab->modify('+1 day'); } return $sp3e45c1; } public function getChartTitle() { $sp5462e3 = $this->_getRangeMinDate(); $spd9afb4 = $this->_getRangeMaxDate(); $sp5952ea = 'Lead\\\'s du ' . $sp5462e3->format('d m Y') . ' au ' . $spd9afb4->format('d m Y'); return $sp5952ea; } protected function _getSqlDateFormat() { $sp5462e3 = $this->_getRangeMinDate(); $spd9afb4 = $this->_getRangeMaxDate(); $sp03715e = $sp5462e3->diff($spd9afb4); $sp43b8d3 = $sp03715e->format('%R%a'); if ($sp43b8d3 < Chart::ZOOM_SWITCH_RANGE) { return '%d/%m/%Y'; } else { return '%d/%m/%Y'; } } protected function _getDateFormat() { $sp5462e3 = $this->_getRangeMinDate(); $spd9afb4 = $this->_getRangeMaxDate(); $sp03715e = $sp5462e3->diff($spd9afb4); $sp43b8d3 = $sp03715e->format('%R%a'); if ($sp43b8d3 < Chart::ZOOM_SWITCH_RANGE) { return 'md'; } else { return 'Ym'; } } protected function _getDateIncrement() { $sp5462e3 = $this->_getRangeMinDate(); $spd9afb4 = $this->_getRangeMaxDate(); $sp03715e = $sp5462e3->diff($spd9afb4); $sp43b8d3 = $sp03715e->format('%R%a'); if ($sp43b8d3 < Chart::ZOOM_SWITCH_RANGE) { return 'day'; } else { return 'month'; } } protected function _getIndexNumber($sp735eb6) { switch ($this->period) { case self::PERIOD_YEAR: return 13; case self::PERIOD_MONTH: $sp5462e3 = $this->_getRangeMinDate(); return cal_days_in_month(CAL_GREGORIAN, $sp5462e3->format('m'), $sp5462e3->format('Y')) + 1; default: throw new \Exception('Unknown timeframe'); } } protected function _getSqlGroupByAggregates() { $sp5462e3 = $this->_getRangeMinDate(); $spd9afb4 = $this->_getRangeMaxDate(); $sp03715e = $sp5462e3->diff($spd9afb4); $sp43b8d3 = $sp03715e->format('%R%a'); if ($sp43b8d3 < Chart::ZOOM_SWITCH_RANGE) { return array('DAY(l.createdAt) as day', 'MONTH(l.createdAt) as month'); } else { return array('MONTH(l.createdAt) as month', 'YEAR(l.createdAt) as year'); } } protected function _getSqlGroupByClause() { $sp5462e3 = $this->_getRangeMinDate(); $spd9afb4 = $this->_getRangeMaxDate(); $sp03715e = $sp5462e3->diff($spd9afb4); $sp43b8d3 = $sp03715e->format('%R%a'); if ($sp43b8d3 < Chart::ZOOM_SWITCH_RANGE) { return 'day, month'; } else { return 'month, year'; } } public function setSpecialGraphIndexes($spf06675) { $sp397db3 = array(); foreach ($spf06675 as $spbf1f67 => $spf4cdf1) { if ($spbf1f67 >= $this->graph_count) { $sp397db3[] = $spbf1f67; } } $this->specialGraphIndexes = $sp397db3; } public function setNormalGraph($spf06675) { $sp397db3 = array(); foreach ($spf06675 as $spbf1f67 => $spf4cdf1) { if ($spbf1f67 < $this->graph_count) { $sp397db3[] = $spf4cdf1[0]; } } $this->normalGraph = $sp397db3; } public function getNormalGraph() { return json_encode($this->normalGraph); } public function loadDemoData($spc4e745 = null) { echo 'Loading demo data
'; $spfab6f9 = $this->container->get('doctrine')->getManager(); if ($spc4e745 == null) { $spdeaad5 = $this->container->get('leadsfactory.form_repository')->findAll(); } else { $spdeaad5 = array($this->container->get('leadsfactory.form_repository')->find($spc4e745)); } foreach ($spdeaad5 as $spa09441) { $sp21ff78 = new \DateTime(); $sp0ee46a = new \DateInterval('P1D'); echo 'Processing form (' . $spa09441->getId() . ' -> ' . $spa09441->getName() . ')
'; echo '--> Deleting leads
'; $sp043d70 = $spfab6f9->getConnection()->prepare('DELETE FROM Leads WHERE form_id = :form_id'); $sp043d70->bindValue('form_id', $spa09441->getId()); $sp043d70->execute(); for ($sp96ec35 = 0; $sp96ec35 < 365; $sp96ec35++) { $sp43cf50 = rand(0, 5); echo '--> Creating Lead DAY : ' . $sp96ec35 . '/365 (form : ' . $spa09441->getId() . ' / number of leads to create : ' . $sp43cf50 . ')
'; $sp21ff78->sub($sp0ee46a); for ($sp060a6e = 0; $sp060a6e <= $sp43cf50; $sp060a6e++) { $sp119292 = new Leads(); $sp119292->setFirstname('firstname-(' . $sp060a6e . '/' . $sp43cf50 . ')-' . rand()); $sp119292->setLastname('lastname-' . rand()); $sp119292->setStatus(1); $sp119292->setFormType($spa09441->getFormType()); $sp119292->setForm($spa09441); $sp119292->setCreatedAt($sp21ff78); $spfab6f9->persist($sp119292); $spfab6f9->flush(); unset($sp119292); } $this->createPageViewsForDemo($sp43cf50, $spa09441, $sp21ff78); } } } protected function createPageViewsForDemo($sp43cf50, $spa09441, $sp21ff78) { $spc8adac = rand(1, 99); $sp88c542 = $spc8adac / 100 * $sp43cf50 + $sp43cf50; for ($sp060a6e = 0; $sp060a6e <= $sp88c542; $sp060a6e++) { $spa7186a = new Tracking(); $sp105c3f = rand(0, 1); if ($sp105c3f) { $sp0128e6 = rand(1, 5); $sp0128e6 = 'demo_utm_code_' . $sp0128e6; $spa7186a->setUtmCampaign($sp0128e6); } $spa7186a->setForm($spa09441); $spa7186a->setCreatedAt($sp21ff78); $spfab6f9 = $this->container->get('doctrine')->getManager(); $spfab6f9->persist($spa7186a); $spfab6f9->flush(); unset($spa7186a); unset($sp105c3f); unset($sp0128e6); } unset($sp88c542); unset($spc8adac); } }