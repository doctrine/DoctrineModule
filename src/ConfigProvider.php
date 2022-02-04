<?php

declare(strict_types=1);

namespace DoctrineModule;

use Doctrine\Common\Cache;
use Laminas\Authentication\Storage\Session as LaminasSessionStorage;

/**
 * Config provider for DoctrineORMModule config
 */
final class ConfigProvider
{
    /**
     * @return mixed[]
     */
    public function __invoke(): array
    {
        return [
            'doctrine' => $this->getDoctrineConfig(),
            'doctrine_factories' => $this->getDoctrineFactoryConfig(),
            'dependencies' => $this->getDependencyConfig(),
            'validators' => $this->getValidatorConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration
     *
     * @return mixed[]
     */
    public function getDependencyConfig(): array
    {
        return [
            'invokables' => ['DoctrineModule\Authentication\Storage\Session' => LaminasSessionStorage::class],
            'factories' => ['doctrine.cli' => Service\CliFactory::class],
            'abstract_factories' => ['DoctrineModule' => ServiceFactory\AbstractDoctrineServiceFactory::class],
        ];
    }

    /**
     * Default configuration for Doctrine module
     *
     * @return mixed[]
     */
    public function getDoctrineConfig(): array
    {
        return [
            'cache' => [
                'apc' => [
                    'class'     => Cache\ApcCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'apcu' => [
                    'class'     => Cache\ApcuCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'array' => [
                    'class' => Cache\ArrayCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'filesystem' => [
                    'class'     => Cache\FilesystemCache::class,
                    'directory' => 'data/DoctrineModule/cache',
                    'namespace' => 'DoctrineModule',
                ],
                'memcache' => [
                    'class'     => Cache\MemcacheCache::class,
                    'instance'  => 'my_memcache_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'memcached' => [
                    'class'     => Cache\MemcachedCache::class,
                    'instance'  => 'my_memcached_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'predis' => [
                    'class'     => Cache\PredisCache::class,
                    'instance'  => 'my_predis_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'redis' => [
                    'class'     => Cache\RedisCache::class,
                    'instance'  => 'my_redis_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'wincache' => [
                    'class'     => Cache\WinCacheCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'xcache' => [
                    'class'     => Cache\XcacheCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'zenddata' => [
                    'class'     => Cache\ZendDataCache::class,
                    'namespace' => 'DoctrineModule',
                ],
            ],

            //These authentication settings are a hack to tide things over until version 1.0
            //Normall doctrineModule should have no mention of odm or orm
            'authentication' => [
                //default authentication options should be set in either the odm or orm modules
                'odm_default' => [],
                'orm_default' => [],
            ],
            'authenticationadapter' => [
                'odm_default' => true,
                'orm_default' => true,
            ],
            'authenticationstorage' => [
                'odm_default' => true,
                'orm_default' => true,
            ],
            'authenticationservice' => [
                'odm_default' => true,
                'orm_default' => true,
            ],
        ];
    }

    /**
     * Factory mappings - used to define which factory to use to instantiate a particular doctrine service type
     *
     * @return mixed[]
     */
    public function getDoctrineFactoryConfig(): array
    {
        return [
            'cache'                 => Service\CacheFactory::class,
            'eventmanager'          => Service\EventManagerFactory::class,
            'driver'                => Service\DriverFactory::class,
            'authenticationadapter' => Service\Authentication\AdapterFactory::class,
            'authenticationstorage' => Service\Authentication\StorageFactory::class,
            'authenticationservice' => Service\Authentication\AuthenticationServiceFactory::class,
        ];
    }

    /**
     * @return mixed[]
     */
    public function getValidatorConfig(): array
    {
        return [
            'aliases'   => [
                'DoctrineNoObjectExists' => Validator\NoObjectExists::class,
                'DoctrineObjectExists'   => Validator\ObjectExists::class,
                'DoctrineUniqueObject'   => Validator\UniqueObject::class,
            ],
            'factories' => [
                Validator\NoObjectExists::class => Validator\Service\NoObjectExistsFactory::class,
                Validator\ObjectExists::class   => Validator\Service\ObjectExistsFactory::class,
                Validator\UniqueObject::class   => Validator\Service\UniqueObjectFactory::class,
            ],
        ];
    }
}
