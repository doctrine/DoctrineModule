<?php

declare(strict_types=1);

namespace DoctrineModule;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Doctrine\Common\Cache as DoctrineCache;
use DoctrineModule\Cache\LaminasStorageCache;
use Laminas\Authentication\Storage\Session as LaminasSessionStorage;
use Laminas\Cache\Storage\Adapter\Memory;

/**
 * Config provider for DoctrineORMModule config
 */
final class ConfigProvider
{
    /** @return array<non-empty-string, mixed[]> */
    public function __invoke(): array
    {
        return [
            'caches' => $this->getCachesConfig(),
            'doctrine' => $this->getDoctrineConfig(),
            'doctrine_factories' => $this->getDoctrineFactoryConfig(),
            'dependencies' => $this->getDependencyConfig(),
            'validators' => $this->getValidatorConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration
     *
     * @return array<non-empty-string, array<non-empty-string, class-string>>
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
     * @return array<non-empty-string, mixed[]>
     */
    public function getDoctrineConfig(): array
    {
        return [
            'cache' => $this->getDoctrineCacheConfig(),

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
     * @return array<non-empty-string, class-string>
     */
    public function getDoctrineFactoryConfig(): array
    {
        return [
            'cache' => Service\CacheFactory::class,
            'eventmanager' => Service\EventManagerFactory::class,
            'driver' => Service\DriverFactory::class,
            'authenticationadapter' => Service\Authentication\AdapterFactory::class,
            'authenticationstorage' => Service\Authentication\StorageFactory::class,
            'authenticationservice' => Service\Authentication\AuthenticationServiceFactory::class,
        ];
    }

    /** @return array<non-empty-string, mixed[]> */
    public function getValidatorConfig(): array
    {
        return [
            'aliases' => [
                'DoctrineNoObjectExists' => Validator\NoObjectExists::class,
                'DoctrineObjectExists' => Validator\ObjectExists::class,
                'DoctrineUniqueObject' => Validator\UniqueObject::class,
            ],
            'factories' => [
                Validator\NoObjectExists::class => Validator\Service\NoObjectExistsFactory::class,
                Validator\ObjectExists::class => Validator\Service\ObjectExistsFactory::class,
                Validator\UniqueObject::class => Validator\Service\UniqueObjectFactory::class,
            ],
        ];
    }

    /** @return array<non-empty-string, array{adapter: string, options?: mixed[], plugins?: mixed[]}> */
    public function getCachesConfig(): array
    {
        $defaultOptions = ['namespace' => 'DoctrineModule'];

        return [
            'doctrinemodule.cache.apcu' => [
                'adapter' => 'apcu',
                'options' => $defaultOptions,
            ],
            'doctrinemodule.cache.array' => [
                'adapter' => Memory::class,
                'options' => $defaultOptions,
            ],
            'doctrinemodule.cache.filesystem' => [
                'adapter' => 'filesystem',
                'options' => $defaultOptions + [
                    'cache_dir' => 'data/DoctrineModule/cache',
                    // We need to be slightly less restrictive than Filesystem defaults:
                    'key_pattern' => '/^[a-z0-9_\+\-\[\]\\\\$#]*$/Di',
                ],
                'plugins' => [['name' => 'serializer']],
            ],
            'doctrinemodule.cache.memcached' => [
                'adapter' => 'memcached',
                'options' => $defaultOptions + ['servers' => []],
            ],
            'doctrinemodule.cache.redis' => [
                'adapter' => 'redis',
                'options' => $defaultOptions + [
                    'server' => [
                        'host' => 'localhost',
                        'post' => 6379,
                    ],
                ],
            ],
        ];
    }

    /**
     * Use doctrine/cache config, when doctrine/cache:^1.0 is installed, and use laminas/laminas-cache,
     * when doctrine/cache:^2.0 is installed, as the latter does not include any cache adapters anymore
     *
     * @return array<non-empty-string,array{class:class-string,instance?:string,namespace?:string,directory?:string}>
     */
    private function getDoctrineCacheConfig(): array
    {
        if (InstalledVersions::satisfies(new VersionParser(), 'doctrine/cache', '^1.0')) {
            return [
                'apc' => [
                    'class' => DoctrineCache\ApcCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'apcu' => [
                    'class' => DoctrineCache\ApcuCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'array' => [
                    'class' => DoctrineCache\ArrayCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'filesystem' => [
                    'class' => DoctrineCache\FilesystemCache::class,
                    'directory' => 'data/DoctrineModule/cache',
                    'namespace' => 'DoctrineModule',
                ],
                'memcache' => [
                    'class' => DoctrineCache\MemcacheCache::class,
                    'instance' => 'my_memcache_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'memcached' => [
                    'class' => DoctrineCache\MemcachedCache::class,
                    'instance' => 'my_memcached_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'predis' => [
                    'class' => DoctrineCache\PredisCache::class,
                    'instance' => 'my_predis_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'redis' => [
                    'class' => DoctrineCache\RedisCache::class,
                    'instance' => 'my_redis_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'wincache' => [
                    'class' => DoctrineCache\WinCacheCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'xcache' => [
                    'class' => DoctrineCache\XcacheCache::class,
                    'namespace' => 'DoctrineModule',
                ],
                'zenddata' => [
                    'class' => DoctrineCache\ZendDataCache::class,
                    'namespace' => 'DoctrineModule',
                ],
            ];
        }

        return [
            'apcu' => [
                'class' => LaminasStorageCache::class,
                'instance' => 'doctrinemodule.cache.apcu',
            ],
            'array' => [
                'class' => LaminasStorageCache::class,
                'instance' => 'doctrinemodule.cache.array',
            ],
            'filesystem' => [
                'class' => LaminasStorageCache::class,
                'instance' => 'doctrinemodule.cache.filesystem',
            ],
            'memcached' => [
                'class' => LaminasStorageCache::class,
                'instance' => 'doctrinemodule.cache.memcached',
            ],
            'redis' => [
                'class' => LaminasStorageCache::class,
                'instance' => 'doctrinemodule.cache.redis',
            ],
        ];
    }
}
