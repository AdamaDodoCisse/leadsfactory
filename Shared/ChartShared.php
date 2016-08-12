<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 07:58
 */

namespace Tellaw\LeadsFactoryBundle\Shared;


use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\Tracking;
use Tellaw\LeadsFactoryBundle\Utils\Chart;

class ChartShared
{

    /**
     * @return array
     */
    public function getSpecialGraphIndexes()
    {
        return $this->specialGraphIndexes;
    }

    /**
     * @return int
     */
    public function getGraphCount()
    {
        return $this->graph_count;
    }

    /**
     * @param string $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param $formType
     * @internal param array $formTypes
     */
    public function setFormType($formType)
    {
        if (empty($formType)) {
            $this->formType = $this->_getAllFormTypes();
        } else {
            $this->formType = (empty($formType) || is_array($formType)) ? $formType : array($formType);
        }
    }

    /**
     * @return array
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @param $formType
     * @internal param array $formTypes
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return array
     */
    public function getForm()
    {
        return $this->form;
    }


    /**
     * Fetch leads counts grouped by form type
     *
     * @return array
     */
    protected function _loadLeadsDataByTypes()
    {

        $em = $this->container->get('doctrine')->getManager();
        $data = array();

        foreach ($this->formType as $formType) {

            if (!is_object($formType)) {
                $formType = $this->getContainer()->get('leadsfactory.form_type_repository')->findOneById($formType);
            }

            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();
            $qb->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))
                ->from('TellawLeadsFactoryBundle:Leads', 'l')
                ->where('l.formType = :form_type_id')
                ->andWhere('l.createdAt >= :minDate')
                ->andWhere('l.createdAt <= :maxDate')
                ->groupBy($this->_getSqlGroupByClause())
                ->setParameter('format', $this->_getSqlDateFormat())
                ->setParameter('form_type_id', $formType->getId())
                ->setParameter('minDate', $this->_getRangeMinDate()->format('Y-m-d'))
                ->setParameter('maxDate', $this->_getRangeMaxDate()->format('Y-m-d'));
            $qb = $this->excludeInternalLeads($qb);
            $results = $qb->getQuery()->getResult();

            array_unshift($results, $formType->getName());
            $data[$formType->getName()] = $results;
        }

        return $data;
    }

    /**
     * Fetch leads counts grouped by forms
     *
     * @return array
     */
    protected function _loadLeadsDataByFormsType()
    {
        $minDate = $this->_getRangeMinDate()->format('Y-m-d H:i:s');
        $em = $this->container->get('doctrine')->getManager();
        $data = array();

        $formTypeId = $this->formType[0];

        $forms = $this->container->get('leadsfactory.form_repository')->findByFormType($formTypeId);

        foreach ($forms as $form) {
            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();
            $qb->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))
                ->from('TellawLeadsFactoryBundle:Leads', 'l')
                ->where('l.form = :form_id')
                ->andWhere('l.createdAt >= :minDate')
                ->groupBy($this->_getSqlGroupByClause())
                ->setParameter('format', $this->_getSqlDateFormat())
                ->setParameter('form_id', $form->getId())
                ->setParameter('minDate', $minDate);
            $qb = $this->excludeInternalLeads($qb);
            $results = $qb->getQuery()->getResult();

            array_unshift($results, $form->getName());
            $data[$form->getId()] = $results;
        }

        return $data;
    }

    /**
     * Fetch leads counts for a form
     *
     * @return array
     */
    protected function _loadLeadsDataByForm()
    {
        $minDate = $this->_getRangeMinDate()->format('Y-m-d H:i:s');
        $em = $this->container->get('doctrine')->getManager();
        $data = array();

        foreach ($this->form as $form) {
            if (!($form instanceof Form)) {
                $form = $this->container->get('leadsfactory.form_repository')->findOneById($form);
            }
            if ($form === null) {
                continue;
            }

            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();
            $qb->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))
                ->from('TellawLeadsFactoryBundle:Leads', 'l')
                ->where('l.form = :form_id')
                ->andWhere('l.createdAt >= :minDate')
                ->andWhere('l.createdAt <= :maxDate')
                ->groupBy($this->_getSqlGroupByClause())
                ->setParameter('format', $this->_getSqlDateFormat())
                ->setParameter('form_id', $form->getId())
                ->setParameter('minDate', $this->_getRangeMinDate()->format('Y-m-d'))
                ->setParameter('maxDate', $this->_getRangeMaxDate()->format('Y-m-d'));
            $qb = $this->excludeInternalLeads($qb);
            $results = $qb->getQuery()->getResult();

            array_unshift($results, $form->getName());
            $data[$form->getName()] = $results;
        }

        return $data;
    }

    /**
     * Get min date of period
     *
     * @return \DateTime
     */
    protected function _getRangeMinDate()
    {

        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        /** @var Tellaw\LeadsFactoryBundle\Entity\UserPreferences $userPreferences */
        $userPreferences = $utils->getUserPreferences();

        return clone($userPreferences->getDataPeriodMinDate());

    }

    /**
     * Get max date of period
     *
     * @return \DateTime
     */
    protected function _getRangeMaxDate()
    {

        /** @var Tellaw\LeadsFactoryBundle\Utils\LFUtils $utils */
        $utils = $this->container->get('lf.utils');

        /** @var Tellaw\LeadsFactoryBundle\Entity\UserPreferences $userPreferences */
        $userPreferences = $utils->getUserPreferences();

        return $userPreferences->getDataPeriodMaxDate();

    }

    /**
     * Fetch form types
     *
     * @return array
     */
    protected function _getAllFormTypes()
    {
        $em = $this->container->get('doctrine')->getManager();
        $formTypes = $this->getContainer()->get('leadsfactory.form_type_repository')->findAll();

        return $formTypes;
    }

    /**
     * Format data for google chart plugin
     *
     * @param $data
     * @return array
     *
     * Origine :
     *
     * array (size=2)
     * 'Demande info ' =>
     * array (size=366)
     * 0 => string 'Demande info ' (length=17)
     * 1 =>
     * array (size=4)
     * 'date' => string '201501' (length=6)
     * 'n' => string '5' (length=1)
     * 'day' => string '1' (length=1)
     * 'month' => string '1' (length=1)
     * 2 =>
     * array (size=4)
     * 'date' => string '201502' (length=6)
     * 'n' => string '6' (length=1)
     * 'day' => string '1' (length=1)
     * 'month' => string '2' (length=1)
     *
     * Cible :
     *
     *                 var data = new google.visualization.DataTable();
     * var raw_data = [    ["Demande info ",6,9,7,8,7,7,9,8,4,5,7,6,11,0],
     *                      ["Type de d\u00e9monstration",2,3,4,2,2,1,2,4,6,2,3,2,4,0],
     *                      ["Moyenne",9.4,9.4,9.4,9.4,9.4,9.4,9.4,9.4,9.4,9.4,9.4,9.4,9.4,9.4],
     *                      ["Total",8,12,11,10,9,8,11,12,10,7,10,8,15,0] ]
     *
     * var range =     ["Jun 13","Jul 13","Aug 13","Sep 13","Oct 13","Nov 13","Dec 13","Jan 14","Feb 14","Mar 14","Apr 14","May 14","Jun 14","Jul 14","Aug 14","Sep 14","Oct 14","Nov 14","Dec 14","Jan 15","Feb 15","Mar 15"]
     *
     */

    protected function _formatChartData($data)
    {

        // determine if we are working on days of monthes
        $minDate = $this->_getRangeMinDate();
        $maxDate = $this->_getRangeMaxDate();

        $diff = $minDate->diff($maxDate);
        $nbDiffDays = $diff->format('%R%a');
        $nbDiffMonths = $diff->format('%R%m');

        if ($nbDiffDays < Chart::ZOOM_SWITCH_RANGE) {
            // Nous affichons à la journée
            $workingOnDays = true;
        } else {
            $workingOnDays = false;
        }

        // Array to build formated with target content.
        $targetArray = array();
        $timeRange = array();

        $initialLoop = true;

        // Loop over Objects availables
        foreach ($data as $graphObject) {

            $type = array_shift($graphObject);
            $targetArray [$type] = array();

            // Reset days counter
            $startingDate = $this->_getRangeMinDate();

            // Create loop for building empty dates of timeframe

            // determine if we are working on days of monthes
            if ($workingOnDays) {

                // Building empty slots for every days
                for ($a = 0; $a <= $nbDiffDays; $a++) {

                    if ($initialLoop) $timeRange[] = (string)$startingDate->format('d/m/Y');

                    $targetArray [$type][(string)$startingDate->format('d/m/Y')] = 0;
                    $startingDate->add(new \DateInterval('P1D'));
                }
                $initialLoop = false;

                // filling with good values
                foreach ($graphObject as $object) {
                    $targetArray [$type][(string)$object['date']] = $object['n'];
                }

            }

        }

        // extract TimeRange
        $this->graphTimeRange = $timeRange;

        $googleFormat = array();
        // Extract Data to Google Format
        $nbGraph = 0;
        foreach ($targetArray as $key => $graphObject) {

            $googleFormat[$nbGraph] = array();
            $googleFormat[$nbGraph][] = $key;

            foreach ($graphObject as $graphElement) {

                $googleFormat[$nbGraph][] = (int)$graphElement;
            }
            $nbGraph++;
        }

        return $googleFormat;

    }

    /**
     * Add average and total graphs if necessary
     *
     * @param array $chartData
     * @return array
     */
    protected function _addAdditionalGraphs($chartData)
    {

        $this->graph_count = count($chartData);

        $utils = $this->container->get("lf.utils");
        $user_preferences = $utils->getUserPreferences();

        if ($user_preferences->getDataDisplayAverage()) {
            // En mode d'affichage d'un formulaire ou d'un type unique on ajoute la courbe moyenne
            //if(count($chartData) <= 1 || count($this->formType) == 1 )
            $chartData[] = $this->_addAverageGraph($chartData);
        }

        if ($user_preferences->getDataDisplayTotal()) {
            //En mode d'affichage d'un type, on ajoute la courbe qui totalise les valeurs de chacun des formulaires
            //if(count($this->formType) == 1 && $this->graph_count > 1)
            $chartData[] = $this->_addTotalGraph($chartData);
        }

        //Set les courbes "spéciales" pour distinction dans le template
        //if(count($chartData) != $this->graph_count) {
        $this->setSpecialGraphIndexes($chartData);
        $this->setNormalGraph($chartData);

        //}

        return $chartData;
    }


    /**
     * Add total graph
     *
     * @param array $chartData
     * @return array
     */
    protected function _addTotalGraph($chartData)
    {
        $graphLength = count($chartData[0]);
        $total = array('Total');
        for ($i = 1; $i < $graphLength; $i++) {
            $value = 0;
            for ($j = 0; $j < $this->graph_count; $j++) {
                $value += $chartData[$j][$i];
            }
            $total[$i] = $value;
        }

        return $total;
    }

    /**
     * Add average graph
     *
     * @param $chartData
     * @return array
     */
    protected function _addAverageGraph($chartData)
    {
        $graphLength = count($chartData[0]);
        $nbGraphs = count($chartData);
        $value = 0;

        $moyenne = array('Moyenne');
        for ($i = 1; $i < $graphLength; $i++) {
            $value = 0;
            foreach ($chartData as $graphData) {
                $value += $graphData[$i];

            }
            $moyenne[] = $value / $nbGraphs;
        }

        return $moyenne;
    }

    /**
     * Get chart time range
     *
     * @return array
     */
    public function getTimeRange()
    {

        return json_encode($this->graphTimeRange);
    }

    /**
     * Get year time range
     *
     * @return array
     * @deprecated
     */
    protected function _getYearRange()
    {
        $end = New \DateTime();
        $start = $this->_getRangeMinDate();

        $range = array();
        while ($start <= $end) {
            $range[] = $start->format('M y');
            $start->modify('+1 month');
        }

        return $range;
    }

    /**
     * Get mont time range
     *
     * @return array
     *
     * @deprecated
     */
    protected function _getMonthRange()
    {
        $end = New \DateTime();
        $start = $this->_getRangeMinDate();

        $range = array();
        while ($start <= $end) {
            $range[] = $start->format('d/m');
            $start->modify('+1 day');
        }

        return $range;
    }

    /**
     * @return string
     */
    public function getChartTitle()
    {

        $minDate = $this->_getRangeMinDate();
        $maxDate = $this->_getRangeMaxDate();

        $title = "Lead\'s du " . $minDate->format('d m Y') . " au " . $maxDate->format('d m Y');

        return $title;
    }

    protected function _getSqlDateFormat()
    {

        $minDate = $this->_getRangeMinDate();
        $maxDate = $this->_getRangeMaxDate();

        $diff = $minDate->diff($maxDate);
        $nbDiffDays = $diff->format('%R%a');

        if ($nbDiffDays < Chart::ZOOM_SWITCH_RANGE) {
            // Nous affichons à la journée
            return '%d/%m/%Y';
        } else {
            return '%d/%m/%Y';
        }
    }

    /**
     * @return string
     */
    protected function _getDateFormat()
    {

        $minDate = $this->_getRangeMinDate();
        $maxDate = $this->_getRangeMaxDate();

        $diff = $minDate->diff($maxDate);
        $nbDiffDays = $diff->format('%R%a');

        if ($nbDiffDays < Chart::ZOOM_SWITCH_RANGE) {
            // Nous affichons à la journée
            return 'md';
        } else {
            return 'Ym';
        }
    }

    /**
     * @return string
     */
    protected function _getDateIncrement()
    {

        $minDate = $this->_getRangeMinDate();
        $maxDate = $this->_getRangeMaxDate();

        $diff = $minDate->diff($maxDate);
        $nbDiffDays = $diff->format('%R%a');

        if ($nbDiffDays < Chart::ZOOM_SWITCH_RANGE) {
            // Nous affichons à la journée
            return 'day';
        } else {
            return 'month';
        }

    }

    /**
     * Return number of records to display
     *
     * @param Datetime $date
     * @return int
     */
    protected function _getIndexNumber($date)
    {
        switch ($this->period) {
            case self::PERIOD_YEAR:
                return 13;
            case self::PERIOD_MONTH:
                $minDate = $this->_getRangeMinDate();

                return (cal_days_in_month(CAL_GREGORIAN, $minDate->format('m'), $minDate->format('Y')) + 1);
            default:
                throw new \Exception('Unknown timeframe');
        }
    }

    /**
     * @return array
     */
    protected function _getSqlGroupByAggregates()
    {

        $minDate = $this->_getRangeMinDate();
        $maxDate = $this->_getRangeMaxDate();

        $diff = $minDate->diff($maxDate);
        $nbDiffDays = $diff->format('%R%a');

        if ($nbDiffDays < Chart::ZOOM_SWITCH_RANGE) {
            return array('DAY(l.createdAt) as day', 'MONTH(l.createdAt) as month');
        } else {
            return array('MONTH(l.createdAt) as month', 'YEAR(l.createdAt) as year');
        }

    }

    /**
     * @return string
     */
    protected function _getSqlGroupByClause()
    {

        $minDate = $this->_getRangeMinDate();
        $maxDate = $this->_getRangeMaxDate();

        $diff = $minDate->diff($maxDate);
        $nbDiffDays = $diff->format('%R%a');

        if ($nbDiffDays < Chart::ZOOM_SWITCH_RANGE) {

            // Nous affichons à la journée
            return 'day, month';

        } else {

            return 'month, year';

        }

    }

    /**
     * @param $chartData
     */
    public function setSpecialGraphIndexes($chartData)
    {
        $specials = array();
        foreach ($chartData as $key => $data) {
            if ($key >= ($this->graph_count)) {
                $specials[] = $key;
            }
        }
        $this->specialGraphIndexes = $specials;
    }

    public function setNormalGraph($chartData)
    {

        $specials = array();
        foreach ($chartData as $key => $data) {
            if ($key < ($this->graph_count)) {
                $specials[] = $data[0];
            }
        }
        $this->normalGraph = $specials;
    }

    public function getNormalGraph()
    {
        return json_encode($this->normalGraph);
    }

    // TODO: move to fixtures or remove
    public function loadDemoData($formId = null)
    {

        echo("Loading demo data\r\n");

        $em = $this->container->get('doctrine')->getManager();

        if ($formId == null) {
            $forms = $this->container->get('leadsfactory.form_repository')->findAll();
        } else {
            $forms = array($this->container->get('leadsfactory.form_repository')->find($formId));
        }

        // Loop over forms
        foreach ($forms as $form) {

            $day = new \DateTime();
            $dateInterval = new \DateInterval('P1D');

            echo("Processing form (" . $form->getId() . " -> " . $form->getName() . ")\r\n");

            echo("--> Deleting leads\r\n");

            // Delete leads for form
            $query = $em->getConnection()->prepare('DELETE FROM Leads WHERE form_id = :form_id');
            $query->bindValue('form_id', $form->getId());
            $query->execute();

            // Reload leads for two years
            for ($i = 0; $i < 365; $i++) {

                // Random a number of leads for that day beetween 0 and 20
                $leadsNumberForDay = rand(0, 5);

                echo("--> Creating Lead DAY : " . $i . "/365 (form : " . $form->getId() . " / number of leads to create : " . $leadsNumberForDay . ")\r\n");

                $day->sub($dateInterval);

                for ($j = 0; $j <= $leadsNumberForDay; $j++) {

                    $lead = new Leads();
                    $lead->setFirstname("firstname-(" . $j . "/" . $leadsNumberForDay . ")-" . rand());
                    $lead->setLastname("lastname-" . rand());
                    $lead->setStatus(1);
                    $lead->setFormType($form->getFormType());
                    $lead->setForm($form);
                    $lead->setCreatedAt($day);
                    $em->persist($lead);


                    unset ($lead);
                }

                // Ajout des listes
                $this->createPageViewsForDemo($leadsNumberForDay, $form, $day);

            }

        }

        $em->flush();
    }

    // TODO: move to fixtures or remove
    protected function createPageViewsForDemo($leadsNumberForDay, $form, $day)
    {

        // Now create page views
        //echo ("--> Creating page views for the day\r\n");

        // Calculate % of variation
        $variation = rand(1, 99);

        // Calculate number of page views
        $nbPageViews = ($variation / 100) * $leadsNumberForDay + $leadsNumberForDay;


        for ($j = 0; $j <= $nbPageViews; $j++) {

            //echo ("--> Creating Page view : ".$j."/".$nbPageViews." (form : ".$form->getId().")\r\n");

            // write them
            $tracking = new Tracking();

            // random if UTM is origin (1) or not (0)
            $hasUtm = rand(0, 1);

            // if utm is not origin, calculate it from 1 to 5;
            if ($hasUtm) {
                $utm_campaign = rand(1, 5);
                $utm_campaign = "demo_utm_code_" . $utm_campaign;
                $tracking->setUtmCampaign($utm_campaign);
            }

            $tracking->setForm($form);
            $tracking->setCreatedAt($day);

            $em = $this->container->get('doctrine')->getManager();
            $em->persist($tracking);
            $em->flush();

            unset ($tracking);
            unset ($hasUtm);
            unset ($utm_campaign);

        }

        unset ($nbPageViews);
        unset ($variation);

    }

}
