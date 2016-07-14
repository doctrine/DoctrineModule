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

namespace DoctrineModule;

use Doctrine\Common\Cache;
use Zend\Authentication\Storage\Session;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * Return general-purpose zend-i18n configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'console'            => $this->getConsoleConfig(),
            'controllers'        => $this->getControllerConfig(),
            'dependencies'       => $this->getDependencyConfig(),
            'doctrine'           => $this->getDoctrineConfig(),
            'doctrine_factories' => $this->getDoctrineFactoryConfig(),
            'route_manager'      => $this->getRouteManagerConfig(),
        ];
    }

    /**
     * @return array
     */
    public function getConsoleConfig()
    {
        return [
            'router' => [
                'routes' => [
                    'doctrine_cli' => [
                        'type' => 'symfony_cli',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\CliController::class => Service\CliControllerFactory::class,
            ],
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'DoctrineModule\Authentication\Storage\Session' => Session::class,
            ],
            'factories' => [
                'doctrine.cli' => Service\CliFactory::class,
                Session::class => InvokableFactory::class,
            ],
            'abstract_factories' => [
                'DoctrineModule' => ServiceFactory\AbstractDoctrineServiceFactory::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getDoctrineConfig()
    {
        return [
            'cache' => [
                'apcu' => [
                    'class'     => Cache\ApcuCache::class,
                    'namespace' => __NAMESPACE__,
                ],
                'array' => [
                    'class'     => Cache\ArrayCache::class,
                    'namespace' => __NAMESPACE__,
                ],
                'filesystem' => [
                    'class'     => Cache\FilesystemCache::class,
                    'directory' => 'data/DoctrineModule/cache',
                    'namespace' => __NAMESPACE__,
                ],
                'memcache' => [
                    'class'     => Cache\MemcacheCache::class,
                    'instance'  => 'my_memcache_alias',
                    'namespace' => __NAMESPACE__,
                ],
                'memcached' => [
                    'class'     => Cache\MemcachedCache::class,
                    'instance'  => 'my_memcached_alias',
                    'namespace' => __NAMESPACE__,
                ],
                'predis' => [
                    'class'     => Cache\PredisCache::class,
                    'instance'  => 'my_predis_alias',
                    'namespace' => __NAMESPACE__,
                ],
                'redis' => [
                    'class'     => Cache\RedisCache::class,
                    'instance'  => 'my_redis_alias',
                    'namespace' => __NAMESPACE__,
                ],
                'wincache' => [
                    'class'     => Cache\WinCacheCache::class,
                    'namespace' => __NAMESPACE__,
                ],
                'xcache' => [
                    'class'     => Cache\XcacheCache::class,
                    'namespace' => __NAMESPACE__,
                ],
                'zenddata' => [
                    'class'     => Cache\ZendDataCache::class,
                    'namespace' => __NAMESPACE__,
                ],
            ],

            // These authentication settings are a hack to tide things over until version 1.0
            // Normal doctrineModule should have no mention of odm or orm
            'authentication' => [
                // default authentication options should be set in either the odm or orm modules
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
     * @return array
     */
    public function getDoctrineFactoryConfig()
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
     * @return array
     */
    public function getRouteManagerConfig()
    {
        return [
            'factories' => [
                'symfony_cli' => Service\SymfonyCliRouteFactory::class,
            ],
        ];
    }
}
