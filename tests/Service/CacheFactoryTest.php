<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\PredisCache;
use DoctrineModule\Cache\LaminasStorageCache;
use DoctrineModule\Service\CacheFactory;
use Laminas\Cache\ConfigProvider;
use Laminas\Cache\Storage\Adapter\Memory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Predis\ClientInterface;

use function assert;

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
        if (! InstalledVersions::satisfies(new VersionParser(), 'doctrine/cache', '^1.0.0')) {
            $this->markTestSkipped('This test requires doctrine/cache:^1.0, which is not installed.');
        }

        $factory        = new CacheFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'cache' => [
                        'foo' => [
                            'class' => ArrayCache::class,
                            'namespace' => 'bar',
                        ],
                    ],
                ],
            ]
        );

        $service = $factory->__invoke($serviceManager, ArrayCache::class);
        assert($service instanceof ArrayCache);

        $this->assertInstanceOf(ArrayCache::class, $service);
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
        $config         = [
            'doctrine' => [
                'cache' => [
                    'phpunit' => [
                        'class' => LaminasStorageCache::class,
                        'instance' => 'my-laminas-cache',
                        'namespace' => 'DoctrineModule',
                    ],
                ],
            ],
            'caches' => [
                'my-laminas-cache' => ['adapter' => 'memory'],
            ],
        ];

        // setup for laminas-cache 3 with memory adapter 2
        $serviceManager->configure((new Memory\ConfigProvider())->getServiceDependencies());
        $serviceManager->setService('config', $config);

        $cache = $factory->__invoke($serviceManager, LaminasStorageCache::class);

        $this->assertInstanceOf(LaminasStorageCache::class, $cache);
    }

    public function testCreatePredisCache(): void
    {
        if (! InstalledVersions::satisfies(new VersionParser(), 'doctrine/cache', '^1.0.0')) {
            $this->markTestSkipped('This test requires doctrine/cache:^1.0, which is not installed.');
        }

        $factory        = new CacheFactory('predis');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'cache' => [
                        'predis' => [
                            'class' => PredisCache::class,
                            'instance' => 'my_predis_alias',
                            'namespace' => 'DoctrineModule',
                        ],
                    ],
                ],
            ]
        );
        $serviceManager->setService(
            'my_predis_alias',
            $this->createMock(ClientInterface::class)
        );
        $cache = $factory->__invoke($serviceManager, PredisCache::class);

        $this->assertInstanceOf(PredisCache::class, $cache);
    }

    public function testUseServiceFactory(): void
    {
        if (! InstalledVersions::satisfies(new VersionParser(), 'doctrine/cache', '^1.0.0')) {
            $this->markTestSkipped('This test requires doctrine/cache:^1.0, which is not installed.');
        }

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

        $serviceManager->setFactory(ChainCache::class, static fn () => $mock);

        $cache = $factory->__invoke($serviceManager, ChainCache::class);

        $this->assertSame($mock, $cache);
    }
}
