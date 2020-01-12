<?php

namespace DoctrineModuleTest\ServiceFactory;

use DoctrineModuleTest\ServiceManagerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test that verifies that services are defined correctly
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ModuleDefinedServicesTest extends TestCase
{
    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->serviceManager = ServiceManagerFactory::getServiceManager();
    }

    /**
     * Verifies that the module defines the correct services
     *
     * @dataProvider getServicesThatShouldBeDefined
     */
    public function testModuleDefinedServices($serviceName, $defined)
    {
        $this->assertSame($defined, $this->serviceManager->has($serviceName));
    }

    /**
     * Verifies that the module defines the correct services
     *
     * @dataProvider getServicesThatCanBeFetched
     */
    public function testModuleFetchedService($serviceName, $expectedClass)
    {
        $this->assertInstanceOf($expectedClass, $this->serviceManager->get($serviceName));
    }

    /**
     * Verifies that the module defines the correct services
     *
     * @dataProvider getServicesThatCannotBeFetched
     */
    public function testModuleInvalidService($serviceName)
    {
        $this->expectException('Laminas\ServiceManager\Exception\ServiceNotFoundException');

        $this->serviceManager->get($serviceName);
    }

    /**
     * @return array
     */
    public function getServicesThatShouldBeDefined()
    {
        return [
            ['doctrine.cache.array', true],
            ['doctrine.cache.apc', true],
            ['doctrine.cache.filesystem', true],
            ['doctrine.cache.memcache', true],
            ['doctrine.cache.memcached', true],
            ['doctrine.cache.redis', true],
            ['doctrine.cache.wincache', true],
            ['doctrine.cache.xcache', true],
            ['doctrine.cache.zenddata', true],
            ['doctrine.authenticationadapter.orm_default', true],
            ['doctrine.authenticationstorage.orm_default', true],
            ['doctrine.authenticationservice.orm_default', true],
            ['doctrine.authenticationadapter.odm_default', true],
            ['doctrine.authenticationstorage.odm_default', true],
            ['doctrine.authenticationservice.odm_default', true],
            ['foo', false],
            ['foo.bar', false],
            ['foo.bar.baz', false],
            ['doctrine', false],
            ['doctrine.foo', false],
            ['doctrine.foo.bar', false],
            ['doctrine.cache.bar', false],
            //['doctrine.cache.laminascachestorage'],
        ];
    }

    /**
     * @return array
     */
    public function getServicesThatCanBeFetched()
    {
        return [
            ['doctrine.cache.array', 'Doctrine\Common\Cache\ArrayCache'],
        ];
    }

    /**
     * @return array
     */
    public function getServicesThatCannotBeFetched()
    {
        return [
            ['foo'],
            ['foo.bar'],
            ['foo.bar.baz'],
            ['doctrine'],
            ['doctrine.foo'],
            ['doctrine.foo.bar'],
            ['doctrine.cache.bar'],
        ];
    }
}
