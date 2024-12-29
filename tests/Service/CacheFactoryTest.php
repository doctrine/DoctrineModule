<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use DoctrineModule\Cache\LaminasStorageCache;
use DoctrineModule\Service\CacheFactory;
use Laminas\Cache\ConfigProvider;
use Laminas\Cache\Storage\Adapter\Memory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Test for {@see \DoctrineModule\Service\CacheFactory}
 */
class CacheFactoryTest extends BaseTestCase
{
    /**
     * @covers \DoctrineModule\Service\CacheFactory::__invoke
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
}
