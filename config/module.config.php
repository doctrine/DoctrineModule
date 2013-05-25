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
//            'apc' => array(
//                'namespace' => 'DoctrineModule',
//            ),
//            'array' => array(
//                'namespace' => 'DoctrineModule',
//            ),
//            'filesystem' => array(
//                'directory' => 'data/DoctrineModule/cache',
//                'namespace' => 'DoctrineModule',
//            ),
            'memcache' => array(
                'instance'  => 'my_memcache_instance',
//                'namespace' => 'DoctrineModule',
            ),
            'memcached' => array(
                'instance'  => 'my_memcached_instance',
//                'namespace' => 'DoctrineModule',
            ),
            'redis' => array(
                'instance'  => 'my_redis_alias',
//                'namespace' => 'DoctrineModule',
            ),
//            'wincache' => array(
//                'namespace' => 'DoctrineModule',
//            ),
//            'xcache' => array(
//                'namespace' => 'DoctrineModule',
//            ),
//            'zenddata' => array(
//                'namespace' => 'DoctrineModule',
//            ),
        ),
        
        //These authentication settings are a hack to tide things over until version 1.0
        //Normall doctrineModule should have no mention of odm or orm
        'authentication' => array(
            'adapter' => array(
                'default' => array(
                    'object_manager' => 'doctrine.objectmanager.default',
                    'identity_class' => 'Application\Model\User',
                    'identity_property' => 'username',
                    'credential_property' => 'password'
                )
            ),
            'storage' => array(
                'default' => array(
                    'object_manager' => 'doctrine.objectmanager.default',
                    'identity_class' => 'Application\Model\User',
                )
            ),
            'service' => array(
                'default' => array(
                    'adapter' => 'doctrine.authentication.adapter.default',
                    'storage' => 'doctrine.authentication.storage.default'
                )
            )
        )
    ),

    'service_manager' => array(
        'invokables' => array(
            'DoctrineModule\Authentication\Storage\Session' => 'Zend\Authentication\Storage\Session',
            'doctrine.builder.eventmanager'                 => 'DoctrineModule\Builder\EventManagerBuilder',
            'doctrine.builder.driver'                       => 'DoctrineModule\Builder\DriverBuilder',
            'doctrine.builder.authentication.repository'    => 'DoctrineModule\Builder\Authentication\RepositoryBuilder',
            'doctrine.builder.authentication.adapter'       => 'DoctrineModule\Builder\Authentication\AdapterBuilder',
            'doctrine.builder.authentication.storage'       => 'DoctrineModule\Builder\Authentication\StorageBuilder',
            'doctrine.builder.authentication.service'       => 'DoctrineModule\Builder\Authentication\AuthenticationServiceBuilder',
        ),
        'factories' => array(
            'doctrine.cache.apc'        => 'DoctrineModule\Service\Cache\ApcCacheFactory',
            'doctrine.cache.array'      => 'DoctrineModule\Service\Cache\ArrayCacheFactory',
            'doctrine.cache.filesystem' => 'DoctrineModule\Service\Cache\FilesystemCacheFactory',
            'doctrine.cache.memcache'   => 'DoctrineModule\Service\Cache\MemcacheCacheFactory',
            'doctrine.cache.memcached'  => 'DoctrineModule\Service\Cache\MemcachedCacheFactory',
            'doctrine.cache.redis'      => 'DoctrineModule\Service\Cache\RedisCacheFactory',
            'doctrine.cache.wincache'   => 'DoctrineModule\Service\Cache\WincacheCacheFactory',
            'doctrine.cache.xcache'     => 'DoctrineModule\Service\Cache\XcacheCacheFactory',
            'doctrine.cache.zenddata'   => 'DoctrineModule\Service\Cache\ZendDataCacheFactory',
            'doctrine.cli'              => 'DoctrineModule\Service\CliFactory',
        ),
        'abstract_factories' => array(
            'DoctrineModule' => 'DoctrineModule\Service\DoctrineServiceAbstractFactory',
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
