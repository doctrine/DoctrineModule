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

return array(
    'doctrine' => array(
        'cache' => array(
            'apc' => array(
                'class'     => 'Doctrine\Common\Cache\ApcCache',
                'namespace' => 'DoctrineModule',
            ),
            'array' => array(
                'class' => 'Doctrine\Common\Cache\ArrayCache',
                'namespace' => 'DoctrineModule',
            ),
            'filesystem' => array(
                'class'     => 'Doctrine\Common\Cache\FilesystemCache',
                'directory' => 'data/DoctrineModule/cache',
                'namespace' => 'DoctrineModule',
            ),
            'memcache' => array(
                'class'     => 'Doctrine\Common\Cache\MemcacheCache',
                'instance'  => 'my_memcache_alias',
                'namespace' => 'DoctrineModule',
            ),
            'memcached' => array(
                'class'     => 'Doctrine\Common\Cache\MemcachedCache',
                'instance'  => 'my_memcached_alias',
                'namespace' => 'DoctrineModule',
            ),
            'redis' => array(
                'class'     => 'Doctrine\Common\Cache\RedisCache',
                'instance'  => 'my_redis_alias',
                'namespace' => 'DoctrineModule',
            ),
            'wincache' => array(
                'class'     => 'Doctrine\Common\Cache\WinCacheCache',
                'namespace' => 'DoctrineModule',
            ),
            'xcache' => array(
                'class'     => 'Doctrine\Common\Cache\XcacheCache',
                'namespace' => 'DoctrineModule',
            ),
            'zenddata' => array(
                'class'     => 'Doctrine\Common\Cache\ZendDataCache',
                'namespace' => 'DoctrineModule',
            ),
        ),

        //These authentication settings are a hack to tide things over until version 1.0
        //Normall doctrineModule should have no mention of odm or orm
        'authentication' => array(
            //default authentication options should be set in either the odm or orm modules
            'odm_default' => array(),
            'orm_default' => array(),
        ),
        'authenticationadapter' => array(
            'odm_default' => true,
            'orm_default' => true,
        ),
        'authenticationstorage' => array(
            'odm_default' => true,
            'orm_default' => true,
        ),
        'authenticationservice' => array(
            'odm_default' => true,
            'orm_default' => true,
        )
    ),

    // Factory mappings - used to define which factory to use to instantiate a particular doctrine
    // service type
    'doctrine_factories' => array(
        'cache'                 => 'DoctrineModule\Service\CacheFactory',
        'eventmanager'          => 'DoctrineModule\Service\EventManagerFactory',
        'driver'                => 'DoctrineModule\Service\DriverFactory',
        'authenticationadapter' => 'DoctrineModule\Service\Authentication\AdapterFactory',
        'authenticationstorage' => 'DoctrineModule\Service\Authentication\StorageFactory',
        'authenticationservice' => 'DoctrineModule\Service\Authentication\AuthenticationServiceFactory',
    ),

    'service_manager' => array(
        'invokables' => array(
            'DoctrineModule\Authentication\Storage\Session' => 'Zend\Authentication\Storage\Session'
        ),
        'factories' => array(
            'doctrine.cli' => 'DoctrineModule\Service\CliFactory',
        ),
        'abstract_factories' => array(
            'DoctrineModule' => 'DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory',
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'DoctrineModule\Controller\Cli' => 'DoctrineModule\Service\CliControllerFactory'
        )
    ),

    'route_manager' => array(
        'factories' => array(
            'symfony_cli' => 'DoctrineModule\Service\SymfonyCliRouteFactory',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'doctrine_cli' => array(
                    'type' => 'symfony_cli',
                )
            )
        )
    ),
);
