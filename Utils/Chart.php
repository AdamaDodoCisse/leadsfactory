<?php

namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\Tracking;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Session\Session;
use Tellaw\LeadsFactoryBundle\Shared\ChartShared;

class Chart extends ChartShared {

    const DEBUG_MODE = false;

    /**
     * @var string year|month
     */
    protected $period;

    const PERIOD_YEAR = 'year';
    const PERIOD_MONTH = 'month';

    // disabled for now, but kept for later use
    const ZOOM_SWITCH_RANGE = 9999; // Switch from days to month at a range of 90 values

    protected $graphTimeRange = null;

    /**
     * DateInterval specification
     *
     * @var array
     */
    protected $period_interval = array(
        self::PERIOD_YEAR  => 'P1Y',
        self::PERIOD_MONTH => 'P1M'
    );

    /**
     * @var array
     */
    protected $formType;

    /**
     * @var array
     */
    protected $form;

    /**
     * @var int
     */
    protected $graph_count;

    /**
     * @var array
     */
    protected $specialGraphIndexes;

    protected $normalGraph;

    protected $minDate = null;
    protected $maxDate = null;

    public function __construct () {
    }

    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    protected function excludeInternalLeads(QueryBuilder $qb)
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

        if (Chart::DEBUG_MODE) var_dump ($data);

        $chartData = $this->_formatChartData($data);
        $chartData = $this->_addAdditionalGraphs($chartData);



        $chartData = json_encode($chartData);

        return $chartData;
    }


}
