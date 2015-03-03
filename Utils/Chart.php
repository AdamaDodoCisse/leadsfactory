<?php

namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\Tracking;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Session\Session;

class Chart {

    /**
     * @var string year|month
     */
    private $period;

    const PERIOD_YEAR = 'year';
    const PERIOD_MONTH = 'month';

    /**
     * DateInterval specification
     *
     * @var array
     */
    private $period_interval = array(
        self::PERIOD_YEAR  => 'P1Y',
        self::PERIOD_MONTH => 'P1M'
    );

    /**
     * @var array
     */
    private $formType;

    /**
     * @var array
     */
    private $form;

    /**
     * @var int
     */
    private $graph_count;

    /**
     * @var array
     */
    private $specialGraphIndexes;

    private $minDate = null;
    private $maxDate = null;

    public function __construct () {

        /*
        $session = new Session();
        $session->start();
        if ( $session->has ( 'minDateStatistics' ) && $session->has ( 'minDateStatistics' ) ) {

            $this->minDate = $session->get('minDateStatistics');
            $this->maxDate = $session->get('maxDateStatistics');

        } else {

            $this->minDate = $this->_getRangeMinDate()->format('Y-m-d');

        }
        */

    }

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
        if(empty($formType)){
            $this->formType = $this->_getAllFormTypes();
        }else{
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
     * @var ContainerInterface
     */
    private $container;

    public function setContainer (ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Method used to set the timeframe used by statistics
     */
    public function setTimeFrame ( $minDate, $maxDate ) {



    }

    /**
     * Fetch leads counts grouped by form type
     *
     * @return array
     */
    private function _loadLeadsDataByTypes()
    {
        $minDate = $this->_getRangeMinDate()->format('Y-m-d');
        $em = $this->container->get('doctrine')->getManager();
        $data = array();
        foreach($this->formType as $formType){

            if (!is_object($formType)) {
                $formType = $this->getContainer()->get('leadsfactory.form_type_repository')->findOneById($formType);
            }

            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();
            $qb->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))
               ->from('TellawLeadsFactoryBundle:Leads', 'l')
               ->where('l.formType = :form_type_id')
               ->andWhere('l.createdAt >= :minDate')
               ->groupBy($this->_getSqlGroupByClause())
               ->setParameter('format', $this->_getSqlDateFormat())
               ->setParameter('form_type_id', $formType->getId())
               ->setParameter('minDate', $minDate)
            ;
            $qb = $this->excludeInternalLeads($qb);
            $results = $qb->getQuery()->getResult();

            array_unshift($results,$formType->getName());
            $data[$formType->getName()] = $results;
        }
        return $data;
    }

    /**
     * Fetch leads counts grouped by forms
     *
     * @return array
     */
    private function _loadLeadsDataByFormsType()
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
               ->setParameter('form_type_id', $form->getId())
               ->setParameter('minDate', $minDate)
            ;
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
    private function _loadLeadsDataByForm()
    {
        $minDate = $this->_getRangeMinDate()->format('Y-m-d H:i:s');
        $em = $this->container->get('doctrine')->getManager();
        $data = array();

        foreach ($this->form as $form) {
            if(!($form instanceof Form)) {
                $form = $this->container->get('leadsfactory.form_repository')->findOneById($form);
            }

            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();
            $qb->select(array_merge(array('DATE_FORMAT(l.createdAt,:format) as date', 'count(l) as n'), $this->_getSqlGroupByAggregates()))
               ->from('TellawLeadsFactoryBundle:Leads', 'l')
               ->where('l.form = :form_id')
               ->andWhere('l.createdAt >= :minDate')
               ->groupBy($this->_getSqlGroupByClause())
               ->setParameter('format', $this->_getSqlDateFormat())
               ->setParameter('form_id', $form->getId())
               ->setParameter('minDate', $minDate)
            ;
            $qb = $this->excludeInternalLeads($qb);
            $results = $qb->getQuery()->getResult();

            array_unshift($results, $form->getName());
            $data[$form->getName()] = $results;
        }

        return $data;
    }

    /**
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    private function excludeInternalLeads(QueryBuilder $qb)
    {
        $i = 0;
        foreach ($this->container->getParameter('leadsfactory.internal_email_patterns') as $pattern) {
            $qb->andWhere('l.email not like :pattern_'.$i)
               ->setParameter('pattern_'.$i, $pattern)
            ;
            ++$i;
        }
        return $qb;
    }

    /**
     * Load chart data
     *
     * @return array
     */
    public function loadChartData()
    {
        if(!empty($this->form)){
            // Loads datas from forms in array FORM
            $data = $this->_loadLeadsDataByForm();
        }else{
            if (!empty($this->formType) && count($this->formType) > 1) {
                $data = $this->_loadLeadsDataByTypes();
            } else {
                $data = $this->_loadLeadsDataByFormsType();
            }
        }
        $chartData = $this->_formatChartData($data);
        $chartData = $this->_addAdditionalGraphs($chartData);
        $chartData = json_encode($chartData);

        return $chartData;
    }

    /**
     * Get min date of period
     *
     * @return \DateTime
     */
    private function _getRangeMinDate()
    {
        $minDate = new \DateTime();
        $minDate->sub(new \DateInterval($this->period_interval[$this->period]));

        if($this->period == self::PERIOD_YEAR){
            $minDate->modify('first day of this month');
        }

        return $minDate;
    }

    /**
     * Fetch form types
     *
     * @return array
     */
    private function _getAllFormTypes()
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
     */
    private function _formatChartData($data)
    {
        $chartData = array();
        foreach($data as $formTypeData){
            $type = array_shift($formTypeData);
            $d = array();

            foreach($formTypeData as $c){
                if(array_key_exists('date', $c)){
                    $d[$c['date']] = (int) $c['n'];
                }
            }
            $dateFormat = $this->_getDateFormat();
            $date = new \DateTime();
            for($i=0; $i<$this->_getIndexNumber($date); $i++){

                if(!array_key_exists($date->format($dateFormat), $d)){
                    $d[(int) $date->format($dateFormat)]= 0;
                }
                $date->modify('-1 '.$this->_getDateIncrement());
            }
            ksort($d);
            array_unshift($d, $type);
            $d = array_values($d);
            $chartData[] = $d;
        }

        $this->graph_count = count($chartData);

        return $chartData;
    }

    /**
     * Add average and total graphs if necessary
     *
     * @param array $chartData
     * @return array
     */
    private function _addAdditionalGraphs($chartData)
    {
        // En mode d'affichage d'un formulaire ou d'un type unique on ajoute la courbe moyenne
        //if(count($chartData) <= 1 || count($this->formType) == 1 )
        $chartData[] = $this->_addAverageGraph($chartData);

        //En mode d'affichage d'un type, on ajoute la courbe qui totalise les valeurs de chacun des formulaires
        //if(count($this->formType) == 1 && $this->graph_count > 1)
        $chartData[] = $this->_addTotalGraph($chartData);


        //Set les courbes "spéciales" pour distinction dans le template
        if(count($chartData) != $this->graph_count)
            $this->setSpecialGraphIndexes($chartData);

        return $chartData;
    }

    /**
     * Add total graph
     *
     * @param array $chartData
     * @return array
     */
    private function _addTotalGraph($chartData)
    {
        $graphLength = count($chartData[0]);
        $total = array('Total');
        for($i=1; $i<$graphLength; $i++){
            $value = 0;
            for($j=0; $j<$this->graph_count; $j++){
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
    private function _addAverageGraph($chartData)
    {
        $graphLength = count($chartData[0]);
        $value = 0;
        for($i=1; $i<$graphLength; $i++){
            foreach($chartData as $graphData){
                $value += $graphData[$i];
            }
        }
        $value = round($value/($graphLength -1), 1);
        $average = array_fill(1, $graphLength-1, $value);
        array_unshift($average, 'Moyenne');

        return $average;
    }

    /**
     * Get chart time range
     *
     * @return array
     */
    public function getTimeRange()
    {
        $rangeGetter = '_get'.ucfirst($this->period).'Range';
        $range = $this->$rangeGetter();

        return json_encode($range);
    }

    /**
     * Get year time range
     *
     * @return array
     */
    private function _getYearRange()
    {
        $end = New \DateTime();
        $start = $this->_getRangeMinDate();

        $range = array();
        while($start <= $end){
            $range[] = $start->format('M y');
            $start->modify('+1 month');
        }
        return $range;
    }

    /**
     * Get mont time range
     *
     * @return array
     */
    private function _getMonthRange()
    {
        $end = New \DateTime();
        $start = $this->_getRangeMinDate();

        $range = array();
        while($start <= $end){
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
        switch($this->period){
            case self::PERIOD_YEAR:
                $title = "Nombre de DI sur l\'année";
                break;
            case self::PERIOD_MONTH:
                $title = "Nombre de DI sur le mois";
                break;
            default:
                throw new \Exception('Unknown timeframe');
        }
        return $title;
    }

    private function _getSqlDateFormat()
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                return '%Y%m';
            case self::PERIOD_MONTH:
                return '%m%d';
            default:
                throw new \Exception('Unknown timeframe');
        }
    }

    /**
     * @return string
     */
    private function _getDateFormat()
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                return 'Ym';
            case self::PERIOD_MONTH:
                return 'md';
            default:
                throw new \Exception('Unknown timeframe');
        }
    }

    /**
     * @return string
     */
    private function _getDateIncrement()
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                return 'month';
            case self::PERIOD_MONTH:
                return 'day';
            default:
                throw new \Exception('Unknown timeframe');
        }
    }

    /**
     * Return number of records to display
     *
     * @param Datetime $date
     * @return int
     */
    private function _getIndexNumber($date)
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                return 13;
            case self::PERIOD_MONTH:
                $minDate = $this->_getRangeMinDate();
                return (cal_days_in_month(CAL_GREGORIAN, $minDate->format('m'), $minDate->format('Y')) +1);
            default:
                throw new \Exception('Unknown timeframe');
        }
    }

    /**
     * @return array
     */
    private function _getSqlGroupByAggregates()
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                return array('MONTH(l.createdAt) as month', 'YEAR(l.createdAt) as year');
            case self::PERIOD_MONTH:
                return array('DAY(l.createdAt) as day', 'MONTH(l.createdAt) as month');
            default:
                throw new \Exception('Unknown timeframe');
        }
    }

    /**
     * @return string
     */
    private function _getSqlGroupByClause()
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                return 'month, year';
            case self::PERIOD_MONTH:
                return 'day, month';
            default:
                throw new \Exception('Unknown timeframe');
        }
    }

    /**
     * @param $chartData
     */
    public function setSpecialGraphIndexes($chartData)
    {
        $specials = array();
        foreach($chartData as $key=>$data){
            if($key >= ($this->graph_count)){
                $specials[] = $key;
            }
        }
        $this->specialGraphIndexes = $specials;
    }

    // TODO: move to fixtures or remove
    public function loadDemoData ( $formId = null ) {

        echo ("Loading demo data\r\n");

        $em = $this->container->get('doctrine')->getManager();

        if ($formId == null)
            $forms = $this->container->get('leadsfactory.form_repository')->findAll();
        else
            $forms = array($this->container->get('leadsfactory.form_repository')->find( $formId ));

        // Loop over forms
        foreach ($forms as $form) {

            $day = new \DateTime();
            $dateInterval = new \DateInterval('P1D');

            echo ("Processing form (".$form->getId()." -> ".$form->getName().")\r\n");

            echo ("--> Deleting leads\r\n");

            // Delete leads for form
            $query = $em->getConnection()->prepare('DELETE FROM Leads WHERE form_id = :form_id');
            $query->bindValue('form_id', $form->getId());
            $query->execute();

            // Reload leads for two years
            for ( $i=0; $i<365; $i++ ) {

                // Random a number of leads for that day beetween 0 and 20
                $leadsNumberForDay = rand (0, 5);

                echo ("--> Creating Lead DAY : ".$i."/365 (form : ".$form->getId()." / number of leads to create : ".$leadsNumberForDay.")\r\n");

                $day->sub( $dateInterval );

                for ($j=0; $j<=$leadsNumberForDay; $j++) {

                    $lead = new Leads();
                    $lead->setFirstname("firstname-(".$j."/".$leadsNumberForDay.")-".rand());
                    $lead->setLastname( "lastname-".rand() );
                    $lead->setStatus( 1 );
                    $lead->setFormType( $form->getFormType() );
                    $lead->setForm( $form );
                    $lead->setCreatedAt( $day );
                    $em->persist($lead);
                    $em->flush();

                    unset ($lead);
                }

                // Ajout des listes
                $this->createPageViewsForDemo ( $leadsNumberForDay, $form, $day );

            }

        }


    }

    // TODO: move to fixtures or remove
    private function createPageViewsForDemo ( $leadsNumberForDay, $form, $day ) {

        // Now create page views
        //echo ("--> Creating page views for the day\r\n");

        // Calculate % of variation
        $variation = rand (1, 99);

        // Calculate number of page views
        $nbPageViews = ($variation / 100) * $leadsNumberForDay + $leadsNumberForDay;


        for ($j=0; $j<=$nbPageViews; $j++) {

            //echo ("--> Creating Page view : ".$j."/".$nbPageViews." (form : ".$form->getId().")\r\n");

            // write them
            $tracking = new Tracking();

            // random if UTM is origin (1) or not (0)
            $hasUtm = rand (0,1);

            // if utm is not origin, calculate it from 1 to 5;
            if ($hasUtm) {
                $utm_campaign = rand (1,5);
                $utm_campaign = "demo_utm_code_".$utm_campaign;
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
