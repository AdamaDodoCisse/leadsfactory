<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 22/02/15
 * Time: 08:33
 */

namespace Tellaw\LeadsFactoryBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tellaw\LeadsFactoryBundle\Utils\AlertUtils;

class AlertUtilsTest extends WebTestCase
{

    private $_client = null;
    private $_container = null;
    private $_alertUtils = null;

    public function __construct()
    {

        $this->_client = static::createClient();
        $this->_container = $this->_client->getContainer();
        $this->_alertUtils = $this->_container->get("alertes_utils");

    }

    public function testCheckWarningStatus()
    {

        $rules = array(
            "rules" => array(
                'error' => array(
                    "min" => "1",
                    "max" => "25",
                    "delta" => "50"
                ),
                'warning' => array(
                    "min" => "3",
                    "max" => "23",
                    "delta" => "25"
                )
            )
        );


        // First test must pass
        $valueNow = 6;
        $valueOld = 8;
        $this->assertEquals(
            AlertUtils::$_STATUS_OK,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        /**
         * Testing Warnings
         */
        // Test de variation
        // Variation : 30*25/100 = 7,5
        $valueNow = 21;
        $valueOld = 30;
        $this->assertEquals(
            AlertUtils::$_STATUS_WARNING,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        // Test de valeur max
        // Variation : 30*25/100 = 7,5
        $valueNow = 24;
        $valueOld = 30;
        $this->assertEquals(
            AlertUtils::$_STATUS_WARNING,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        // Test de valeur min
        // Variation : 30*25/100 = 7,5
        $valueNow = 2;
        $valueOld = 2;
        $this->assertEquals(
            AlertUtils::$_STATUS_WARNING,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        /**
         * Testing ERRORS
         */

        // Variation : 30*25/100 = 7,5
        $valueNow = 21;
        $valueOld = 50;
        $this->assertEquals(
            AlertUtils::$_STATUS_ERROR,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        // Test de valeur max
        $valueNow = 26;
        $valueOld = 26;
        $this->assertEquals(
            AlertUtils::$_STATUS_ERROR,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        // Test de valeur min
        $valueNow = 0;
        $valueOld = 0;
        $this->assertEquals(
            AlertUtils::$_STATUS_ERROR,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        /**
         * Testing Unknown
         */
        $rules = array(
            "rules" => array(
                'error' => array(
                    "min" => "1",
                    "max" => "25",
                    "delta" => "50"
                )
            )
        );
        $valueNow = 6;
        $valueOld = 8;
        $this->assertEquals(
            AlertUtils::$_STATUS_UNKNOWN,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

        $rules = array(
            "rules" => array(
                'warning' => array(
                    "min" => "1",
                    "max" => "25",
                    "delta" => "50"
                )
            )
        );
        $valueNow = 6;
        $valueOld = 8;
        $this->assertEquals(
            AlertUtils::$_STATUS_UNKNOWN,
            $this->_alertUtils->checkWarningStatus($valueNow, $valueOld, $rules)
        );

    }

    public function testGetWarningRules()
    {

        $rules = array(
            "rules" => array(
                'error' => array(
                    "min" => "1",
                    "max" => "25",
                    "delta" => "50"
                ),
                'warning' => array(
                    "min" => "3",
                    "max" => "23",
                    "delta" => "25"
                )
            )
        );

        // Asserting that rules are filled
        $this->assertEquals(
            $rules["rules"]["error"],
            $this->_alertUtils->getAlertRules($rules["rules"])
        );

        $rules = array(
            "rules" => array(
                'warning' => array(
                    "min" => "3",
                    "max" => "23",
                    "delta" => "25"
                )
            )
        );

        // Asserting that rules are empty, returning empty objects
        $expected = $rules["rules"]["error"] = array("min" => null, "max" => null, "delta" => null);
        $this->assertEquals(
            $expected,
            $this->_alertUtils->getAlertRules($rules["rules"])
        );

    }

    public function testGetDeltaValue()
    {

        // Variation : 5 max
        $oldValue = 10;
        $currentValue = 5;
        $deltaValue = 50;

        $this->assertEquals(
            false,
            $this->_alertUtils->getDeltaPourcentValue($oldValue, $currentValue, $deltaValue)
        );

        // Variation : 5 max
        $oldValue = 10; // Old value
        $currentValue = 4; // Current value
        $deltaValue = 50; // % of accepted changes

        $this->assertEquals(
            true,
            $this->_alertUtils->getDeltaPourcentValue($oldValue, $currentValue, $deltaValue)
        );

    }

    public function testGetDeltaPourcent()
    {

        $oldValue = 100;
        $currentValue = 50;
        $this->assertEquals(
            50,
            $this->_alertUtils->getDeltaPourcent($oldValue, $currentValue)
        );

        $oldValue = 100;
        $currentValue = 150;
        $this->assertEquals(
            150,
            $this->_alertUtils->getDeltaPourcent($oldValue, $currentValue)
        );

    }

}
