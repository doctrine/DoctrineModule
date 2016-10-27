<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

return [
    'doctrine' => [
        'cache' => [
            'apc' => [
                'class'     => 'Doctrine\Common\Cache\ApcCache',
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
    ],

    // Factory mappings - used to define which factory to use to instantiate a particular doctrine
    // service type
    'doctrine_factories' => [
        'cache'                 => 'DoctrineModule\Service\CacheFactory',
        'eventmanager'          => 'DoctrineModule\Service\EventManagerFactory',
        'driver'                => 'DoctrineModule\Service\DriverFactory',
        'authenticationadapter' => 'DoctrineModule\Service\Authentication\AdapterFactory',
        'authenticationstorage' => 'DoctrineModule\Service\Authentication\StorageFactory',
        'authenticationservice' => 'DoctrineModule\Service\Authentication\AuthenticationServiceFactory',
    ],

    'service_manager' => [
        'invokables' => [
            'DoctrineModule\Authentication\Storage\Session' => 'Zend\Authentication\Storage\Session',
        ],
        'factories' => [
            'doctrine.cli' => 'DoctrineModule\Service\CliFactory',
        ],
        'abstract_factories' => [
            'DoctrineModule' => 'DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory',
        ],
    ],

    'controllers' => [
        'factories' => [
            'DoctrineModule\Controller\Cli' => 'DoctrineModule\Service\CliControllerFactory',
        ],
    ],

    'route_manager' => [
        'factories' => [
            'symfony_cli' => 'DoctrineModule\Service\SymfonyCliRouteFactory',
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'doctrine_cli' => [
                    'type' => 'symfony_cli',
                ],
            ],
        ],
    ],
];
