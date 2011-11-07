<?php
return array(
    'di' => array(
        'definition' => array(
            'class' => array(
                'SpiffyDoctrine\Service\Doctrine' => array(
                    'methods' => array(
                        '__construct' => array(
                            'conn'   => array('type' => false, 'required' => true),
                            'config' => array('type' => false, 'required' => true),
                            'evm'    => array('type' => false, 'required' => false) 
                        )
                    )
                )
            ),
        ),
        'instance' => array(
            'alias' => array(
                'doctrine' => 'SpiffyDoctrine\Service\Doctrine',
            ),
            'doctrine' => array(
                'parameters' => array(
                    'conn' => array(
                        'driver'   => 'pdo_mysql',
                        'host'     => 'localhost',
                        'port'     => '3306', 
                        'user'     => 'testuser',
                        'password' => 'testpassword',
                        'dbname'   => 'testdbname',
                    ),
                    'config' => array(
                        'auto_generate_proxies'     => true,
                        // @todo: figure out how to de_couple the Proxy dir
                        'proxy_dir'                 => __DIR__ . '/src/SpiffyDoctrine/Proxy',
                        'proxy_namespace'           => 'SpiffyDoctrine\Proxy',
                        'metadata_driver_impl'      => array(
                            // 'application_annotation_driver' => array(
                            //     'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                            //     'namespace' => 'My\Entity\Namespace',
                            //     'paths' => array('/path/to/entities'),
                            //     'cache_class' => 'Doctrine\Common\Cache\ArrayCache',
                            // )
                        ),
                        'metadata_cache_impl'       => 'Doctrine\Common\Cache\ArrayCache',
                        'query_cache_impl'          => 'Doctrine\Common\Cache\ArrayCache',
                        'result_cache_impl'         => 'Doctrine\Common\Cache\ArrayCache',
                        'custom_datetime_functions' => array(
                            //array('name' => 'name', 'className' => 'className')
                        ),
                        'custom_numeric_functions'  => array(
                        ),
                        'custom_string_functions'   => array(
                        ),
                        'custom_hydration_modes'    => array(
                            // array('modeName', 'hydrator')
                        ),
                        'named_queries'             => array(
                            // array('name', 'dql')
                        ),
                        'named_native_queries'      => array(
                            // array('name', 'sql', 'rsm')
                        ),
                        'sql_logger'                => null,
                    ),
                    'evm' => array(
                        'subscribers' => array(
                            // 'Gedmo\Sluggable\SluggableListener'
                        )
                    )  
                )
            )
        )
    )
);