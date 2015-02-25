<?php

/**
 * Class de test de la classe Chart
 */

namespace Tellaw\LeadsFactoryBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlertUtilsTest extends WebTestCase {

    private $_client = null;
    private $_container = null;
    private $_alertUtils = null;

    public function __construct () {

        $this->_client = static::createClient();
        $this->_container = $this->_client->getContainer();
        $this->_alertUtils = $this->_container->get("chart");

    }

    /**
     * Add methods to test here.
     */

}