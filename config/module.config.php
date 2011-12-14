<?php
return array(
    'di' => array(
        'definition' => array(
            'class' => array(
                'Memcache' => array(
                    'addServer' => array(
                        'host' => array('type' => false, 'required' => true),
                        'port' => array('type' => false, 'required' => true),
                    )
                ),
                'SpiffyDoctrine\Factory\DocumentManager' => array(
                    'instantiator' => array('SpiffyDoctrine\Factory\DocumentManager', 'get'),
                    'methods' => array(
                        'get' => array(
                            'conn' => array('type' => 'SpiffyDoctrine\Doctrine\ODM\MongoDB\Connection', 'required' => true)
                        )
                    )
                ),
                'SpiffyDoctrine\Factory\EntityManager' => array(
                    'instantiator' => array('SpiffyDoctrine\Factory\EntityManager', 'get'),
                    'methods' => array(
                        'get' => array(
                            'conn' => array('type' => 'SpiffyDoctrine\Doctrine\ORM\Connection', 'required' => true)
                        )
                    )
                ),
            ),
        ),
        'instance' => array(
            'alias' => array(
                // caching
                'doctrine_memcache'       => 'Memcache',
                'doctrine_cache_apc'      => 'Doctrine\Common\Cache\ApcCache',
                'doctrine_cache_array'    => 'Doctrine\Common\Cache\ArrayCache',
                'doctrine_cache_memcache' => 'Doctrine\Common\Cache\MemcacheCache',

                // managers
                'doctrine_mongo' => 'SpiffyDoctrine\Factory\DocumentManager',
                'doctrine_em'    => 'SpiffyDoctrine\Factory\EntityManager',
                
                // configuration
                'mongo_config'       => 'SpiffyDoctrine\Doctrine\ODM\MongoDB\Configuration',
                'mongo_connection'   => 'SpiffyDoctrine\Doctrine\ODM\MongoDB\Connection',
                'mongo_driver_chain' => 'SpiffyDoctrine\Doctrine\ODM\MongoDB\DriverChain',
                'mongo_evm'          => 'SpiffyDoctrine\Doctrine\Common\EventManager',
                
                'orm_config'       => 'SpiffyDoctrine\Doctrine\ORM\Configuration',
                'orm_connection'   => 'SpiffyDoctrine\Doctrine\ORM\Connection',
                'orm_driver_chain' => 'SpiffyDoctrine\Doctrine\ORM\DriverChain',
                'orm_evm'          => 'SpiffyDoctrine\Doctrine\Common\EventManager',
            ),
            'orm_config' => array(
                'parameters' => array(
                    'opts' => array(
                        'auto_generate_proxies'     => true,
                        'proxy_dir'                 => __DIR__ . '/../../../data/SpiffyDoctrine/Proxy',
                        'proxy_namespace'           => 'SpiffyDoctrine\Proxy',
                        'custom_datetime_functions' => array(),
                        'custom_numeric_functions'  => array(),
                        'custom_string_functions'   => array(),
                        'custom_hydration_modes'    => array(),
                        'named_queries'             => array(),
                        'named_native_queries'      => array(),
                    ),
                    'metadataDriver' => 'orm_driver_chain',
                    'metadataCache'  => 'doctrine_cache_array',
                    'queryCache'     => 'doctrine_cache_array',
                    'resultCache'    => null,
                    'logger'         => null,
                )
            ),
            'orm_connection' => array(
                'parameters' => array(
                    'params' => array(
                        'driver'   => 'pdo_mysql',
                        'host'     => 'localhost',
                        'port'     => '3306', 
                        'user'     => 'testuser',
                        'password' => 'testpassword',
                        'dbname'   => 'testdbname',
                    ),
                    'config' => 'orm_config',
                    'evm'    => 'orm_evm',
                    'pdo'    => null
                )
            ),
            'orm_driver_chain' => array(
                'parameters' => array(
                    'drivers' => array(),
                    'cache' => 'doctrine_cache_array'
                )
            ),
            'orm_evm' => array(
                'parameters' => array(
                    'opts' => array(
                        'subscribers' => array()
                    )
                )
            ),
            'mongo_config' => array(
                'parameters' => array(
                    'opts' => array(
                        'auto_generate_proxies'   => true,
                        'proxy_dir'               => __DIR__ . '/../../../data/SpiffyDoctrine/Proxy',
                        'proxy_namespace'         => 'SpiffyDoctrine\Proxy',
                        'auto_generate_hydrators' => true,
                        'hydrator_dir'            => __DIR__ . '/../../../data/SpiffyDoctrine/Hydrators',
                        'hydrator_namespace'      => 'SpiffyDoctrine\Hydrators',
                    ),
                    'metadataDriver' => 'mongo_driver_chain',
                    'metadataCache'  => 'doctrine_cache_array',
                    'logger'         => null,
                )
            ),
            'mongo_connection' => array(
                'parameters' => array(
                    'server'  => null,
                    'options' => array(),
                    'config'  => 'mongo_config',
                    'evm'     => 'mongo_evm',
                )
            ),
            'mongo_driver_chain' => array(
                'parameters' => array(
                    'drivers' => array(),
                    'cache' => 'doctrine_cache_array'
                )
            ),
            'mongo_evm' => array(
                'parameters' => array(
                    'opts' => array(
                        'subscribers' => array()
                    )
                )
            ),
            'doctrine_memcache' => array(
                'parameters' => array(
                    'host' => '127.0.0.1',
                    'port' => '11211'
                )
            ),
            'doctrine_cache_memcache' => array(
                'parameters' => array(
                    'memcache' => 'doctrine_memcache' 
                )
            ),
            'doctrine_mongo' => array(
                'parameters' => array(
                    'conn' => 'mongo_connection',
                )
            ),
            'doctrine_em' => array(
                'parameters' => array(
                    'conn' => 'orm_connection',
                )
            ),
        )
    )
);