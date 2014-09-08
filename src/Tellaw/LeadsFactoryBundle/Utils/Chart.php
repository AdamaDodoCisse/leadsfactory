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
        if(is_null($formType)){
            $this->formType = $this->_getAllFormTypes();
        }else{
            $this->formType = $formType;
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
     * Fetch leads grouped by form type
     *
     * @todo MAJ SQL pour regrouper par mois ou par jour afin d'avoir 0 plutôt que BLANK lorsqu'aucun enregistrement
     * n'est trouvé pour un mois|jour donné.
     *
     * @return array
     */
    private function _loadLeadsData()
    {
        $minDate = $this->_getRangeMinDate()->format('Y-m-d H:i:s');
        $em = $this->container->get('doctrine')->getManager();
        $data = array();
        foreach($this->formType as $formType){

            $query = $em->getConnection()->prepare('SELECT MONTH(createdAt) as month, count(1) as count FROM Leads WHERE form_type_id = :formType AND createdAt >= :minDate GROUP BY MONTH(createdAt)');
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
     * Load chart data
     *
     * @return array
     */
    public function loadChartData()
    {
        $data = $this->_loadLeadsData();
        $chartData = $this->_formatChartData($data);
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
        foreach($data as $formType => $formTypeData){
            $formTypeArray = array($formTypeData[0]);
            for($i=1; $i<=13; $i++){
                if(isset($formTypeData[$i])){
                    $key = $formTypeData[$i]['month'];
                    $formTypeArray[$key]=$formTypeData[$i]['count'];
                }
            }
            $chartData[] = $formTypeArray;
        }

        //complete empty month with 0 an reorder array
        $now=(int)date('m');
        $delta = 13-$now;
        $cleanChartData=array();
        foreach($chartData as &$cd){
            $tmp=array();
            for($i=1; $i<=13; $i++){
                $index = ($i+$delta <= 13)? $i+$delta : ($i+$delta)-13;
                if(!isset($cd[$i])){
                    $tmp[$index]='0';
                    continue;
                }
                $tmp[$index]=$cd[$i];
            }
            ksort($tmp);
            array_unshift($tmp, $cd[0]);
            $cleanChartData[]=$tmp;
        }


        return $cleanChartData;
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
                $title = "Nombre de DI sur l\'année";
        }
        return $title;
    }

    /**
     * Return padding value for completing leads data array with 0
     *
     * @return int
     */
    private function _getRangePaddingValue()
    {
        switch($this->period){
            case self::PERIOD_YEAR:
                $pad = -13;
                break;
            case self::PERIOD_MONTH:
                $pad = -32;
                break;
            default:
                $pad = -13;
        }
        return $pad;
    }
} 