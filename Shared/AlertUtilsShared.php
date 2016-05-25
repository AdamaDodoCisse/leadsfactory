<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 07:58
 */

namespace Tellaw\LeadsFactoryBundle\Shared;


use Tellaw\LeadsFactoryBundle\Utils\AlertUtils;

class AlertUtilsShared {

    /**
     * @param integer $valueNow actual value
     * @param integer $valueOld Value for period -1
     * @param Array $rules of alerts (use getRules from Form and FormType)
     * @return int status of the values
     */
    public function checkWarningStatus ( $valueNow, $valueOld, $rules ) {

        $warningRules = $this->getWarningRules( $rules['rules'] );
        $alertRules = $this->getAlertRules( $rules['rules'] );;

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
                $alertRules["max"]=null;

            if ( !array_key_exists( "delta", $alertRules ) )
                $alertRules["delta"]=null;

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

    /**
     * Function used to return variation % of newvalue compared to old value
     * @param $oldValue
     * @param $currentValue
     * @return float|string
     */
    public function getDeltaPourcent ( $oldValue, $currentValue ) {

        if ($oldValue == 0) return "&laquo; Données indisponibles &raquo;";
        if ($currentValue == 0) return "&laquo; calcul impossible &raquo;";
        $result = ( $currentValue * 100 ) / $oldValue;

        return $result;

    }

    /**
     * setValuesForAlerts
     *
     * This method intends to fill Form or FormType object with its status details (Variation, alerts...)
     * This method uses yesterday and 8 days before value (a week before yesterday)
     *
     * @param $item
     */
    public function setValuesForAlerts($item)
    {
        $form_repository = $this->entity_manager->getRepository('TellawLeadsFactoryBundle:Form');

        if ($item->getType() == "formType") {
            $forms = $form_repository->findByFormType($item->getId());
        } else {
            $form = $form_repository->find($item->getId());
            $forms = array($form);
        }

        // Calculate todays number of leads
        $minDate = new \DateTime();
        $item->todayValue = $this->getLeadsCountForForms( $forms, $minDate );

        // Create yesterday's date object
        $minDate = new \DateTime();
        $minDate = $minDate->sub(new \DateInterval("P01D"));
        $item->yesterdayValue = $this->getLeadsCountForForms( $forms, $minDate );
        $item->textualYesterdayDay = $this->day[$minDate->format('N')]." ". $minDate->format("d")." ". $this->month[$minDate->format('n')];

        // Get the value for week before
        $minDate = new \DateTime();
        $minDate = $minDate->sub(new \DateInterval("P08D"));
        $item->weekBeforeValue = $this->getLeadsCountForForms( $forms, $minDate );;
        $item->textualWeekBeforeDay = $this->day[$minDate->format('N')]." ". $minDate->format("d")." ". $this->month[$minDate->format('n')];

        // Calculte the variation for both lead's counts
        $item->yesterdayVariation = $this->getDeltaPourcent( $item->weekBeforeValue, $item->yesterdayValue );

        // Evaluate the error status of the form / Type.
        $rules = $item->getRules();

        if(empty($rules)){
            $status = AlertUtils::$_STATUS_UNKNOWN;
        }else{
            $status = $this->checkWarningStatus( $item->yesterdayValue, $item->weekBeforeValue,$item->getRules($rules) );
        }

        // Set status value in object
        $item->yesterdayStatus = $status;

        if ( $status == AlertUtils::$_STATUS_ERROR ) {
            $item->yesterdayStatusColor = "pink";
            $item->yesterdayStatusText = "Erreur";
        } else if ( $status == AlertUtils::$_STATUS_WARNING ) {
            $item->yesterdayStatusColor = "yellow";
            $item->yesterdayStatusText = "Attention!";
        } else if ( $status == AlertUtils::$_STATUS_UNKNOWN ) {
            $item->yesterdayStatusColor = "black";
            $item->yesterdayStatusText = "Aucune donnée";
        } else {
            $item->yesterdayStatusColor = "green";
            $item->yesterdayStatusText = "Status OK";
        }
    }

}