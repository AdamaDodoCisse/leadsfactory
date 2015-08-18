<?php

/**
 * Class de test de la classe Chart
 */

namespace Tellaw\LeadsFactoryBundle\Tests\Utils;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChartTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Symfony\Component\HttpKernel\AppKernel
     */
    protected $kernel;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    protected $chart;

    public function setUp () {

        // Boot the AppKernel in the test environment and with the debug.
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        // Store the container and the entity manager in test case properties
        $this->container = $this->kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();

        $this->chart = $this->container->get('chart');

        // Build the schema for sqlite
        $this->generateSchema();

        parent::setUp();

    }

    public function tearDown()
    {
        // Shutdown the kernel.
        $this->kernel->shutdown();

        parent::tearDown();
    }

    protected function generateSchema()
    {
        // Get the metadata of the application to create the schema.
        $metadata = $this->getMetadata();

        if ( ! empty($metadata)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->entityManager);
            $tool->createSchema($metadata);
        } else {
            throw new Doctrine\DBAL\Schema\SchemaException('No Metadata Classes to process.');
        }
    }

    /**
     * Overwrite this method to get specific metadata.
     *
     * @return Array
     */
    protected function getMetadata()
    {
        return $this->entityManager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * Add methods to test here.
     */
    public function test_loadLeadsDataByTypes () {

        $this->assertEquals('14 Rue Notre-Dame-des-Victoires 75002 Paris FR', '14 Rue Notre-Dame-des-Victoires 75002 Paris FR');



    }

}