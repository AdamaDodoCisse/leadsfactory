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
     * @param integer $valueNow actual value
     * @param integer $valueOld Value for period -1
     * @param json $rules of alerts
     * @return int status of the values
     */
    public function checkWarningStatus ( $valueNow, $valueOld, $rules ) {

        $warningRules = $this->getWarningRules( $rules );
        $alertRules = $this->getAlertRules( $rules );

        // Alert Detection
        if ($alertRules["min"] != null && $valueNow <= $alertRules["min"] )
            return AlertUtils::$_STATUS_ERROR;

        if ($alertRules["max"] != null && $valueNow >= $alertRules["max"] )
            return AlertUtils::$_STATUS_ERROR;

        if ($alertRules["delta"] != null && $this->getDeltaPourcent( $valueOld, $valueNow ) > $alertRules["delta"] )
            return AlertUtils::$_STATUS_ERROR;

        // Warning detection
        if ($warningRules["min"] != null && $valueNow <= $warningRules["min"] )
            return AlertUtils::$_STATUS_WARNING;

        if ($warningRules["max"] != null && $valueNow >= $warningRules["max"] )
            return AlertUtils::$_STATUS_WARNING;

        if ($warningRules["delta"] != null && $this->getDeltaPourcent( $valueOld, $valueNow ) > $warningRules["delta"] )
            return AlertUtils::$_STATUS_WARNING;


        return AlertUtils::$_STATUS_OK;

    }

    public function getWarningRules ( $rules ) {

        $warningRules = $rules["warning"];

        if ( !array_key_exists( "min", $warningRules ) )
            $warningRules["min"]=null;

        if ( !array_key_exists( "max", $warningRules ) )
            $warningRules["min"]=null;

        if ( !array_key_exists( "delta", $warningRules ) )
            $warningRules["min"]=null;

        return $warningRules;

    }

    public function getAlertRules ( $rules ) {

        $alertRules = $rules["warning"];

        if ( !array_key_exists( "min", $alertRules ) )
            $alertRules["min"]=null;

        if ( !array_key_exists( "max", $alertRules ) )
            $alertRules["min"]=null;

        if ( !array_key_exists( "delta", $alertRules ) )
            $alertRules["min"]=null;

        return $alertRules;

    }

    public function getDeltaPourcent ( $oldValue, $currentValue ) {

        if ( $currentValue == 0 ) return "-";

        $pourcent = ($currentValue - $oldValue) / $currentValue * 100;
        return $pourcent;

    }

}