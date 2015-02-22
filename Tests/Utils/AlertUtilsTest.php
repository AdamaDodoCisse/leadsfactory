<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 22/02/15
 * Time: 08:33
 */

namespace Tellaw\LeadsFactoryBundle\Tests\Utils;

use Tellaw\LeadsFactoryBundle\Utils\AlertUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlertUtilsTest extends WebTestCase {

    public function testCheckWarningStatus () {

        $rules = array (
            "rules" => array (
                'error' => array (
                    "min" => "1",
                    "max" => "25",
                    "delta" => "50"
                ),
                'warning' => array (
                    "min" => "3",
                    "max" => "23",
                    "delta" => "25"
                )
            )
        );

        $client = static::createClient();
        $container = $client->getContainer();
        $alertUtils = $container->get("alertes_utils");

        // First test must pass
        $valueNow = 6;
        $valueOld = 8;
        $this->assertEquals(
            AlertUtils::$_STATUS_OK,
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
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
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

        // Test de valeur max
        // Variation : 30*25/100 = 7,5
        $valueNow = 24;
        $valueOld = 30;
        $this->assertEquals(
            AlertUtils::$_STATUS_WARNING,
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

        // Test de valeur min
        // Variation : 30*25/100 = 7,5
        $valueNow = 2;
        $valueOld = 2;
        $this->assertEquals(
            AlertUtils::$_STATUS_WARNING,
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

        /**
         * Testing ERRORS
         */

        // Variation : 30*25/100 = 7,5
        $valueNow = 21;
        $valueOld = 50;
        $this->assertEquals(
            AlertUtils::$_STATUS_ERROR,
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

        // Test de valeur max
        $valueNow = 26;
        $valueOld = 26;
        $this->assertEquals(
            AlertUtils::$_STATUS_ERROR,
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

        // Test de valeur min
        $valueNow = 0;
        $valueOld = 0;
        $this->assertEquals(
            AlertUtils::$_STATUS_ERROR,
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

        /**
         * Testing Unknown
         */
        $rules = array (
            "rules" => array (
                'error' => array (
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
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

        $rules = array (
            "rules" => array (
                'warning' => array (
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
            $alertUtils->checkWarningStatus ( $valueNow, $valueOld, $rules )
        );

    }

}