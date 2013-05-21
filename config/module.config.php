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
            'adapter' => array(
                'default' => array(
                    //'object_manager' => 'doctrine.odm.documentmanager.default' || 'doctrine.orm.entitymanager.default',
                    //'identity_class' => 'Application\Model\User',
                    'identity_property' => 'username',
                    'credential_property' => 'password'
                )
            ),
            'storage' => array(
                'default' => array(
                    //'object_manager' => 'doctrine.odm.documentmanager.default' || 'doctrine.orm.entitymanager.default',
                    //'identity_class' => 'Application\Model\User',
                    //'storage' => defaults to 'Zend\Authentication\Storage\SessionStorage',
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
            'doctrine.factory.cache'                     => 'DoctrineModule\Factory\CacheFactory',
            'doctrine.factory.eventmanager'              => 'DoctrineModule\Factory\EventManagerFactory',
            'doctrine.factory.driver'                    => 'DoctrineModule\Factory\DriverFactory',
            'doctrine.factory.authentication.repository' => 'DoctrineModule\Factory\Authentication\RepositoryFactory',
            'doctrine.factory.authentication.adapter'    => 'DoctrineModule\Factory\Authentication\AdapterFactory',
            'doctrine.factory.authentication.storage'    => 'DoctrineModule\Factory\Authentication\StorageFactory',
            'doctrine.factory.authentication.service'    => 'DoctrineModule\Factory\Authentication\AuthenticationServiceFactory',
        ),
        'factories' => array(
            'doctrine.cli' => 'DoctrineModule\Service\CliFactory',
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
