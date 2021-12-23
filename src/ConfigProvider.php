<?php

declare(strict_types=1);

namespace DoctrineModule;

/**
 * Config provider for DoctrineORMModule config
 *
 * @link    www.doctrine-project.org
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
            'invokables' => ['DoctrineModule\Authentication\Storage\Session' => 'Laminas\Authentication\Storage\Session'],
            'factories' => ['doctrine.cli' => 'DoctrineModule\Service\CliFactory'],
            'abstract_factories' => ['DoctrineModule' => 'DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory'],
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
                    'class'     => 'Doctrine\Common\Cache\ApcCache',
                    'namespace' => 'DoctrineModule',
                ],
                'apcu' => [
                    'class'     => 'Doctrine\Common\Cache\ApcuCache',
                    'namespace' => 'DoctrineModule',
                ],
                'array' => [
                    'class' => 'Doctrine\Common\Cache\ArrayCache',
                    'namespace' => 'DoctrineModule',
                ],
                'filesystem' => [
                    'class'     => 'Doctrine\Common\Cache\FilesystemCache',
                    'directory' => 'data/DoctrineModule/cache',
                    'namespace' => 'DoctrineModule',
                ],
                'memcache' => [
                    'class'     => 'Doctrine\Common\Cache\MemcacheCache',
                    'instance'  => 'my_memcache_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'memcached' => [
                    'class'     => 'Doctrine\Common\Cache\MemcachedCache',
                    'instance'  => 'my_memcached_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'predis' => [
                    'class'     => 'Doctrine\Common\Cache\PredisCache',
                    'instance'  => 'my_predis_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'redis' => [
                    'class'     => 'Doctrine\Common\Cache\RedisCache',
                    'instance'  => 'my_redis_alias',
                    'namespace' => 'DoctrineModule',
                ],
                'wincache' => [
                    'class'     => 'Doctrine\Common\Cache\WinCacheCache',
                    'namespace' => 'DoctrineModule',
                ],
                'xcache' => [
                    'class'     => 'Doctrine\Common\Cache\XcacheCache',
                    'namespace' => 'DoctrineModule',
                ],
                'zenddata' => [
                    'class'     => 'Doctrine\Common\Cache\ZendDataCache',
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
            'cache'                 => 'DoctrineModule\Service\CacheFactory',
            'eventmanager'          => 'DoctrineModule\Service\EventManagerFactory',
            'driver'                => 'DoctrineModule\Service\DriverFactory',
            'authenticationadapter' => 'DoctrineModule\Service\Authentication\AdapterFactory',
            'authenticationstorage' => 'DoctrineModule\Service\Authentication\StorageFactory',
            'authenticationservice' => 'DoctrineModule\Service\Authentication\AuthenticationServiceFactory',
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
