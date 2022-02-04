<?php

declare(strict_types=1);

namespace DoctrineModuleTest\ServiceFactory;

use DoctrineModuleTest\ServiceManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test that verifies that services are defined correctly
 */
class ModuleDefinedServicesTest extends TestCase
{
    protected ServiceLocatorInterface $serviceManager;

    protected function setUp(): void
    {
        $this->serviceManager = ServiceManagerFactory::getServiceManager();
    }

    /**
     * Verifies that the module defines the correct services
     *
     * @dataProvider getServicesThatShouldBeDefined
     */
    public function testModuleDefinedServices(string $serviceName, bool $defined): void
    {
        $this->assertSame($defined, $this->serviceManager->has($serviceName));
    }

    /**
     * Verifies that the module defines the correct services
     *
     * @dataProvider getServicesThatCanBeFetched
     */
    public function testModuleFetchedService(string $serviceName, string $expectedClass): void
    {
        $this->assertInstanceOf($expectedClass, $this->serviceManager->get($serviceName));
    }

    /**
     * Verifies that the module defines the correct services
     *
     * @dataProvider getServicesThatCannotBeFetched
     */
    public function testModuleInvalidService(string $serviceName): void
    {
        $this->expectException('Laminas\ServiceManager\Exception\ServiceNotFoundException');

        $this->serviceManager->get($serviceName);
    }

    /**
     * @return mixed[][]
     */
    public function getServicesThatShouldBeDefined(): array
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
     * @return string[][]
     */
    public function getServicesThatCanBeFetched(): array
    {
        return [
            ['doctrine.cache.array', 'Doctrine\Common\Cache\ArrayCache'],
        ];
    }

    /**
     * @return string[][]
     */
    public function getServicesThatCannotBeFetched(): array
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
