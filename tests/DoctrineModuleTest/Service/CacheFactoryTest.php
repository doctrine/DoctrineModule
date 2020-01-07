<?php

namespace DoctrineModuleTest\Service;

use Doctrine\Common\Cache\ChainCache;
use DoctrineModule\Service\CacheFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Laminas\ServiceManager\ServiceManager;

/**
 * Test for {@see \DoctrineModule\Service\CacheFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class CacheFactoryTest extends BaseTestCase
{
    /**
     * @covers \DoctrineModule\Service\CacheFactory::createService
     */
    public function testWillSetNamespace()
    {
        $factory        = new CacheFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                 'doctrine' => [
                     'cache' => [
                         'foo' => [
                             'namespace' => 'bar',
                         ],
                     ],
                 ],
            ]
        );

        /* @var $service \Doctrine\Common\Cache\ArrayCache */
        $service = $factory->createService($serviceManager);

        $this->assertInstanceOf('Doctrine\\Common\\Cache\\ArrayCache', $service);
        $this->assertSame('bar', $service->getNamespace());
    }

    /**
     * @covers \DoctrineModule\Service\CacheFactory::createService
     * @group 547
     */
    public function testCreateLaminasCache()
    {
        $factory        = new CacheFactory('phpunit');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'cache' => [
                        'phpunit' => [
                            'class' => 'DoctrineModule\Cache\ZendStorageCache',
                            'instance' => 'my-zend-cache',
                            'namespace' => 'DoctrineModule',
                        ],
                    ],
                ],
                'caches' => [
                    'my-zend-cache' => [
                        'adapter' => [
                            'name' => 'blackhole',
                        ],
                    ],
                ],
            ]
        );
        $serviceManager->addAbstractFactory('Laminas\Cache\Service\StorageCacheAbstractServiceFactory');

        $cache = $factory->createService($serviceManager);

        $this->assertInstanceOf('DoctrineModule\Cache\ZendStorageCache', $cache);
    }

    public function testCreatePredisCache()
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

    public function testUseServiceFactory()
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

        $serviceManager->setFactory(ChainCache::class, function () use ($mock) {
            return $mock;
        });

        $cache = $factory->createService($serviceManager);

        $this->assertSame($mock, $cache);
    }
}
