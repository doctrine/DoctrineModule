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
                        'auto-generate-proxies'     => true,
                        // @todo: figure out how to de-couple the Proxy dir
                        'proxy-dir'                 => __DIR__ . '/src/SpiffyDoctrine/Proxy',
                        'proxy-namespace'           => 'SpiffyDoctrine\Proxy',
                        'metadata-driver-impl'      => array(
                            // 'application-annotation-driver' => array(
                            //     'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                            //     'namespace' => 'My\Entity\Namespace',
                            //     'paths' => array('/path/to/entities'),
                            //     'cache-class' => 'Doctrine\Common\Cache\ArrayCache',
                            // )
                        ),
                        'metadata-cache-impl'       => 'Doctrine\Common\Cache\ArrayCache',
                        'query-cache-impl'          => 'Doctrine\Common\Cache\ArrayCache',
                        'result-cache-impl'         => 'Doctrine\Common\Cache\ArrayCache',
                        'custom-datetime-functions' => array(
                            //array('name' => 'name', 'className' => 'className')
                        ),
                        'custom-numeric-functions'  => array(
                        ),
                        'custom-string-functions'   => array(
                        ),
                        'custom-hydration-modes'    => array(
                            // array('modeName', 'hydrator')
                        ),
                        'named-queries'             => array(
                            // array('name', 'dql')
                        ),
                        'named-native-queries'      => array(
                            // array('name', 'sql', 'rsm')
                        ),
                        'sql-logger'                => null,
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