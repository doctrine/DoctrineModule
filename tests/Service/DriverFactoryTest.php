<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver as MongoDBODMAnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver as MongoDBODMAttributeDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as ORMAnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver as ORMAttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use DoctrineModule\Service\DriverFactory;
use DoctrineModuleTest\Service\Mock\MetadataDriverMock;
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
                        'testDriver' => ['class' => MetadataDriverMock::class],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->__invoke($serviceManager, MetadataDriverMock::class);
        $this->assertInstanceOf(MetadataDriverMock::class, $driver);
    }

    public function testCreateDriverChain(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => MetadataDriverMock::class],
                        'testChainDriver' => [
                            'class' => MappingDriverChain::class,
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
        $driver  = $factory->__invoke($serviceManager, MappingDriverChain::class);
        $this->assertInstanceOf(MappingDriverChain::class, $driver);
        assert($driver instanceof MappingDriverChain);

        $drivers = $driver->getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertArrayHasKey('Foo\Bar', $drivers);
        $this->assertInstanceOf(MetadataDriverMock::class, $drivers['Foo\Bar']);
    }

    /**
     * @requires PHP 8.0
     */
    public function testCreateORMAttributeDriver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => ORMAttributeDriver::class],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->__invoke($serviceManager, ORMAttributeDriver::class);
        $this->assertInstanceOf(ORMAttributeDriver::class, $driver);
    }

    /**
     * @requires PHP 8.0
     */
    public function testCreateMongoDBODMAttributeDriver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => MongoDBODMAttributeDriver::class],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->__invoke($serviceManager, MongoDBODMAttributeDriver::class);
        $this->assertInstanceOf(MongoDBODMAttributeDriver::class, $driver);
    }

    public function testCreateORMAnnotationDriver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => ORMAnnotationDriver::class],
                    ],
                ],
            ]
        );
        $serviceManager->setService(
            'doctrine.cache.array',
            new ArrayCache()
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->__invoke($serviceManager, ORMAnnotationDriver::class);
        $this->assertInstanceOf(ORMAnnotationDriver::class, $driver);
        $this->assertInstanceOf(Reader::class, $driver->getReader());
    }

    public function testCreateMongoDBODMAnnotationDriver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => MongoDBODMAnnotationDriver::class],
                    ],
                ],
            ]
        );
        $serviceManager->setService(
            'doctrine.cache.array',
            new ArrayCache()
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->__invoke($serviceManager, MongoDBODMAnnotationDriver::class);
        $this->assertInstanceOf(MongoDBODMAnnotationDriver::class, $driver);
        $this->assertInstanceOf(Reader::class, $driver->getReader());
    }
}
