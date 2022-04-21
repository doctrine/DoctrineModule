<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver as AnnotationDriverODM;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as AnnotationDriverORM;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use DoctrineModule\Service\DriverFactory;
use DoctrineModuleTest\Service\Mock\MetadataDriverMock;
use DoctrineModuleTest\Service\TestAsset\DummyAnnotationDriver;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

use function assert;
use function method_exists;

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
        //$serviceManager->setService('doctrine.cache.array', new ArrayCa);
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
    public function testCreateAttributeDriver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => AttributeDriver::class],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->__invoke($serviceManager, AttributeDriver::class);
        $this->assertInstanceOf(AttributeDriver::class, $driver);
    }

    /**
     * @return array<int, array<string>>
     */
    public function dataProviderAnnotationDrivers(): array
    {
        return [
            [DummyAnnotationDriver::class],
            [AnnotationDriverORM::class],
            [AnnotationDriverODM::class],
        ];
    }

    /**
     * @dataProvider dataProviderAnnotationDrivers
     */
    public function testCreateAnnotationDrivers(string $annotationDriverClassName): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('doctrine.cache.array', new ArrayCache());
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'driver' => [
                        'testDriver' => ['class' => $annotationDriverClassName],
                    ],
                ],
            ]
        );

        $factory = new DriverFactory('testDriver');
        $driver  = $factory->__invoke($serviceManager, $annotationDriverClassName);
        $this->assertInstanceOf($annotationDriverClassName, $driver);

        if (! method_exists($driver, 'getReader')) {
            return;
        }

        $this->assertInstanceOf(Reader::class, $driver->getReader());
    }
}
