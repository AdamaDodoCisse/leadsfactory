<?php
namespace Tellaw\LeadsFactoryBundle\Utils;



/**
 * Class AlertUtils
 *
 * This class intends to provide methods to check the status of objects and calculate its values
 *
 * @package Tellaw\LeadsFactoryBundle\Utils
 */
class AlertUtils {

    public static $_STATUS_UNKNOWN = 0;
    public static $_STATUS_OK = 1;
    public static $_STATUS_WARNING = 2;
    public static $_STATUS_ERROR = 3;
    public static $_STATUS_DATA_PROBLEM = 4; // Not used yet

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param integer $valueNow actual value
     * @param integer $valueOld Value for period -1
     * @param Array $rules of alerts (use getRules from Form and FormType)
     * @return int status of the values
     */
    public function checkWarningStatus ( $valueNow, $valueOld, $rules ) {

        $warningRules = $this->getWarningRules( $rules['rules'] );
        $alertRules = $this->getAlertRules( $rules['rules'] );

        if ( count ($alertRules) > 0 ) {

            // Alert Detection
            if ($alertRules["min"] != null && $valueNow <= $alertRules["min"] )
                return AlertUtils::$_STATUS_ERROR;

            if ($alertRules["max"] != null && $valueNow >= $alertRules["max"] )
                return AlertUtils::$_STATUS_ERROR;

            if ($alertRules["delta"] != null && $this->getDeltaPourcentValue ( $valueOld, $valueNow, $alertRules["delta"] ) )
                return AlertUtils::$_STATUS_ERROR;

        } else {
            return AlertUtils::$_STATUS_UNKNOWN;
        }

        if ( count ($warningRules) > 0 ) {

            // Warning detection
            if ($warningRules["min"] != null && $valueNow <= $warningRules["min"] )
                return AlertUtils::$_STATUS_WARNING;

            if ($warningRules["max"] != null && $valueNow >= $warningRules["max"] )
                return AlertUtils::$_STATUS_WARNING;

            if ($warningRules["delta"] != null && $this->getDeltaPourcentValue( $valueOld, $valueNow, $warningRules["delta"] ) )
                return AlertUtils::$_STATUS_WARNING;

        } else {
            return AlertUtils::$_STATUS_UNKNOWN;
        }

        return AlertUtils::$_STATUS_OK;

    }

    /**
     * This method will return an Array containing formated rules of Warning
     * @param Array $rules.
     * @return Array formatted
     */
    public function getWarningRules ( $rules ) {

        $warningRules = isset($rules['warning']) ? $rules['warning'] : false;

        if ( is_array($warningRules) ) {

            if ( !array_key_exists( "min", $warningRules ) )
                $warningRules["min"]=null;

            if ( !array_key_exists( "max", $warningRules ) )
                $warningRules["min"]=null;

            if ( !array_key_exists( "delta", $warningRules ) )
                $warningRules["min"]=null;

            return $warningRules;

        }

        return array();

    }

    /**
     * This method will return an Array containing formated rules of Alerts
     * @param Array $rules.
     * @return Array formatted
     */
    public function getAlertRules ( $rules ) {

        $alertRules = isset($rules['error']) ? $rules['error'] : false;

        if ( is_array($alertRules) ) {

            if ( !array_key_exists( "min", $alertRules ) )
                $alertRules["min"]=null;

            if ( !array_key_exists( "max", $alertRules ) )
                $alertRules["min"]=null;

            if ( !array_key_exists( "delta", $alertRules ) )
                $alertRules["min"]=null;

            return $alertRules;

        }

        return array();

    }

    /**
     * Test that current value is inside the possible variation of ol value
     * @param $oldValue
     * @param $currentValue
     * @param $deltaValue
     * @return float|string
     */
    public function getDeltaPourcentValue ( $oldValue, $currentValue, $deltaValue ) {

        if ( $deltaValue == 0 ) return "&laquo;NAN&raquo;";

        // calculate variation of first value
        // FirstValue * DeltaPourcentValue / 100 = Delta
        $value = ($oldValue * $deltaValue) / 100;

        //throw new \Exception ("Error : ".($oldValue - $value)." - ".$currentValue. " - ".($oldValue + $value));

        // Current value is smaller then last value including maximum variation decreasing
        if ( ($oldValue - $value) > $currentValue )
            return true;

        // Current value is higher then last value including maximum variation increasing
        if ( ($oldValue + $value) < $currentValue )
            return true;

        // Match delta changes criterias
        return false;

    }

    public function setValuesForAlerts ( $item ) {

        $itemClass = get_class($item);

        $em = $this->container->get("doctrine")->getManager();

        if( strstr ($itemClass, 'Tellaw\LeadsFactoryBundle\Entity\FormType')) {
            $forms = $this->container->get('leadsfactory.form_repository')->findByFormType($item->getId());
        }else{
            $form = $this->container->get('leadsfactory.form_repository')->find($item->getId());
            $forms = array($form);
        }

        $minDate = new \DateTime();
        $minDate = $minDate->sub(new \DateInterval("P01D"))->format('Y-m-d');

        $value = 0;

        foreach($forms as $form){

            $query = $em->getConnection()->prepare('SELECT count(1) as count FROM Leads WHERE form_id = :form_id AND createdAt >= :minDate GROUP BY DAY(createdAt)');
            $query->bindValue('minDate', $minDate);
            $query->bindValue('form_id', $form->getId());
            $query->execute();
            $results = $query->fetchAll();

            if (count ($results)>0)
                $value = $results[0]["count"] + $value;
        }

        // Set the value
        $item->yesterdayValue = $value;

        // Get the value for week before
        $minDate = new \DateTime();
        $minDate = $minDate->sub(new \DateInterval("P09D"))->format('Y-m-d');

        $value = 0;

        foreach($forms as $form){

            $query = $em->getConnection()->prepare('SELECT count(1) as count FROM Leads WHERE form_id = :form_id AND createdAt >= :minDate GROUP BY DAY(createdAt)');
            $query->bindValue('minDate', $minDate);
            $query->bindValue('form_id', $form->getId());
            $query->execute();
            $results = $query->fetchAll();


            if (count ($results))
                $value = $results[0]["count"] + $value;
        }

        // Set the value
        $item->weekBeforeValue = $value;

        $item->yesterdayVariation = $this->getDeltaPourcent( $item->weekBeforeValue, $item->yesterdayValue );

        $rules = $item->getRules();

        if(empty($rules)){
            $status = AlertUtils::$_STATUS_ERROR;
        }else{
            $status = $this->checkWarningStatus( $item->yesterdayValue, $item->weekBeforeValue,$item->getRules($rules) );
        }

        if ( $status == AlertUtils::$_STATUS_ERROR ) {

            $item->yesterdayStatusColor = "pink";
            $item->yesterdayStatusText = "Erreur";

        } else if ( $status == AlertUtils::$_STATUS_WARNING ) {

            $item->yesterdayStatusColor = "yellow";
            $item->yesterdayStatusText = "Attention!";

        } else {
            $item->yesterdayStatusColor = "green";
            $item->yesterdayStatusText = "Status OK";
        }

    }

}