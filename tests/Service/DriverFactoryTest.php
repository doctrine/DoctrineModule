<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use DoctrineModule\Service\DriverFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

use function assert;

/**
 * Base test case to be used when a service manager instance is required
 */
class DriverFactoryTest extends BaseTestCase
{
    public function testCreateDriver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => 'DoctrineModuleTest\Service\Mock\MetadataDriverMock'],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->createService($serviceManager);
        $this->assertInstanceOf('DoctrineModuleTest\Service\Mock\MetadataDriverMock', $driver);
    }

    public function testCreateDriverChain(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => 'DoctrineModuleTest\Service\Mock\MetadataDriverMock'],
                        'testChainDriver' => [
                            'class' => 'Doctrine\Persistence\Mapping\Driver\MappingDriverChain',
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
        $this->assertInstanceOf('Doctrine\Persistence\Mapping\Driver\MappingDriverChain', $driver);
        assert($driver instanceof MappingDriverChain);
        $drivers = $driver->getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertArrayHasKey('Foo\Bar', $drivers);
        $this->assertInstanceOf('DoctrineModuleTest\Service\Mock\MetadataDriverMock', $drivers['Foo\Bar']);
    }

    /**
     * @requires PHP 8.0
     */
    public function testCreateAttributeDriver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => 'Doctrine\ORM\Mapping\Driver\AttributeDriver'],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->createService($serviceManager);
        $this->assertInstanceOf('Doctrine\ORM\Mapping\Driver\AttributeDriver', $driver);
    }
}
