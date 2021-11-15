<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use DoctrineModule\Service\CacheFactory;
use Laminas\Cache\ConfigProvider;
use Laminas\Cache\Storage\Adapter\BlackHole;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

use function assert;
use function class_exists;

/**
 * Test for {@see \DoctrineModule\Service\CacheFactory}
 */
class CacheFactoryTest extends BaseTestCase
{
    /**
     * @covers \DoctrineModule\Service\CacheFactory::createService
     */
    public function testWillSetNamespace(): void
    {
        $factory        = new CacheFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'cache' => [
                        'foo' => ['namespace' => 'bar'],
                    ],
                ],
            ]
        );

        $service = $factory->createService($serviceManager);
        assert($service instanceof ArrayCache);

        $this->assertInstanceOf('Doctrine\\Common\\Cache\\ArrayCache', $service);
        $this->assertSame('bar', $service->getNamespace());
    }

    /**
     * @covers \DoctrineModule\Service\CacheFactory::createService
     * @group 547
     */
    public function testCreateLaminasCache(): void
    {
        $factory        = new CacheFactory('phpunit');
        $serviceManager = new ServiceManager((new ConfigProvider())->getDependencyConfig());

        if (class_exists(BlackHole\ConfigProvider::class)) {
            $serviceManager->configure((new BlackHole\ConfigProvider())->getServiceDependencies());
        }

        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'cache' => [
                        'phpunit' => [
                            'class' => 'DoctrineModule\Cache\LaminasStorageCache',
                            'instance' => 'my-laminas-cache',
                            'namespace' => 'DoctrineModule',
                        ],
                    ],
                ],
                'caches' => [
                    'my-laminas-cache' => ['adapter' => 'blackhole'],
                ],
            ],
        );

        $cache = $factory->createService($serviceManager);

        $this->assertInstanceOf('DoctrineModule\Cache\LaminasStorageCache', $cache);
    }

    public function testCreatePredisCache(): void
    {
        $factory        = new CacheFactory('predis');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'cache' => [
                        'predis' => [
                            'class' => 'Doctrine\Common\Cache\PredisCache',
                            'instance' => 'my_predis_alias',
                            'namespace' => 'DoctrineModule',
                        ],
                    ],
                ],
            ]
        );
        $serviceManager->setService(
            'my_predis_alias',
            $this->createMock('Predis\ClientInterface')
        );
        $cache = $factory->createService($serviceManager);

        $this->assertInstanceOf('Doctrine\Common\Cache\PredisCache', $cache);
    }

    public function testUseServiceFactory(): void
    {
        $factory        = new CacheFactory('chain');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'cache' => [
                        'chain' => [
                            'class' => ChainCache::class,
                        ],
                    ],
                ],
            ]
        );

        $mock = $this->createMock(ChainCache::class);

        $serviceManager->setFactory(ChainCache::class, static function () use ($mock) {
            return $mock;
        });

        $cache = $factory->createService($serviceManager);

        $this->assertSame($mock, $cache);
    }
}
