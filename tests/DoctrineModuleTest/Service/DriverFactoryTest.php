<?php

namespace DoctrineModuleTest\Service;

use DoctrineModule\Service\DriverFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * Base test case to be used when a service manager instance is required
 */
class DriverFactoryTest extends BaseTestCase
{
    public function testCreateDriver()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => [
                            'class' => 'DoctrineModuleTest\Service\Mock\MetadataDriverMock',
                        ],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->createService($serviceManager);
        $this->assertInstanceOf('DoctrineModuleTest\Service\Mock\MetadataDriverMock', $driver);
    }

    public function testCreateDriverChain()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => [
                            'class' => 'DoctrineModuleTest\Service\Mock\MetadataDriverMock',
                        ],
                        'testChainDriver' => [
                            'class' => 'Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain',
                            'drivers' => [
                                'Foo\Bar' => 'testDriver',
                                'Foo\Baz' => null,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testChainDriver');
        $driver  = $factory->createService($serviceManager);
        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain', $driver);
        $drivers = $driver->getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertArrayHasKey('Foo\Bar', $drivers);
        $this->assertInstanceOf('DoctrineModuleTest\Service\Mock\MetadataDriverMock', $drivers['Foo\Bar']);
    }
}
