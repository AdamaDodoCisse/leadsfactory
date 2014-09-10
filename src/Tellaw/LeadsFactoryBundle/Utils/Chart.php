<?php

namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var Form
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
        $this->form = (is_numeric($form)) ? $this->getContainer()->get('doctrine')->getRepository('TellawLeadsFactoryBundle:Form')->findOneById($form) : $form;
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
     * Fetch leads counts grouped by form type
     *
     * @return array
     */
    private function _loadLeadsDataByTypes()
    {
        $minDate = $this->_getRangeMinDate()->format('Y-m-d H:i:s');
        $em = $this->container->get('doctrine')->getManager();
        $data = array();
        foreach($this->formType as $formType){

            if(!is_object($formType))
                $formType = $em->getRepository('TellawLeadsFactoryBundle:FormType')->findOneById($formType);

            $query = $em->getConnection()->prepare('SELECT DATE_FORMAT(createdAt,:format) as date, count(1) as count FROM Leads WHERE form_type_id = :formType AND createdAt >= :minDate '.$this->_getSqlGroupByClause());
            $query->bindValue('format', $this->_getSqlDateFormat());
            $query->bindValue('minDate', $minDate);
            $query->bindValue('formType', $formType->getId());
            $query->execute();
            $results = $query->fetchAll();
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

        $forms = $em->getRepository('TellawLeadsFactoryBundle:Form')->findByFormType($formTypeId);

        foreach($forms as $form){

            $query = $em->getConnection()->prepare('SELECT DATE_FORMAT(createdAt,:format) as date, count(1) as count FROM Leads WHERE form_id = :form_id AND createdAt >= :minDate '.$this->_getSqlGroupByClause());
            $query->bindValue('format', $this->_getSqlDateFormat());
            $query->bindValue('minDate', $minDate);
            $query->bindValue('form_id', $form->getId());
            $query->execute();
            $results = $query->fetchAll();
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

        $query = $em->getConnection()->prepare('SELECT DATE_FORMAT(createdAt,:format) as date, count(1) as count FROM Leads WHERE form_id = :form_id AND createdAt >= :minDate '.$this->_getSqlGroupByClause());
        $query->bindValue('format', $this->_getSqlDateFormat());
        $query->bindValue('minDate', $minDate);
        $query->bindValue('form_id', $this->form->getId());
        $query->execute();
        $results = $query->fetchAll();
        array_unshift($results, $this->form->getName());
        $data[$this->form->getName()] = $results;

        return $data;
    }

    /**
     * Load chart data
     *
     * @return array
     */
    public function loadChartData()
    {
        if(!empty($this->form)){
            $data = $this->_loadLeadsDataByForm();
        }else{
            $data = (empty($this->formType) || count($this->formType) > 1) ? $this->_loadLeadsDataByTypes() : $this->_loadLeadsDataByFormsType();
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
        $formTypes = $em->getRepository('TellawLeadsFactoryBundle:FormType')->findAll();

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
                    $d[$c['date']] = (int) $c['count'];
                }
            }
            $dateFormat = $this->_getDateFormat();
            $now = date($dateFormat);
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
        if(count($chartData) <= 1 || count($this->formType) == 1 )
            $chartData[] = $this->_addAverageGraph($chartData);

        //En mode d'affichage d'un type, on ajoute la courbe qui totalise les valeurs de chacun des formulaires
        if(count($this->formType) == 1 && $this->graph_count > 1){
            $chartData[] = $this->_addTotalGraph($chartData);
        }

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
        }
    }

    /**
     * @return string
     */
    private function _getSqlGroupByClause()
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                return 'GROUP BY MONTH(createdAt)';
            case self::PERIOD_MONTH:
                return 'GROUP BY DAY(createdAt)';
        }
    }

    /**
     * @param $chartData
     */
    public function setSpecialGraphIndexes($chartData)
    {
        $specials = array();
        foreach($chartData as $key=>$data){
            if($key >= (count($chartData) - $this->graph_count))
                $specials[] = $key;
        }
        $this->specialGraphIndexes = $specials;
    }

} 