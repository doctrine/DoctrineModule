<?php

return array(
    'di' => array(
        'definition' => array(
            'class' => array(
                'SpiffyDoctrine\EntityManagerFactory' => array(
                    'instantiator' => array('SpiffyDoctrine\EntityManagerFactory', 'create'),
                    'methods' => array(
                        'create' => array(
                            'name' => array(
                                'type' => 'array',
                                'required' => true
                            ),
                            'container' => array(
                                'type' => 'SpiffyDoctrine\Container',
                                'required' => true
                            )
                        ),
                    )
                )
            ),
        ),
        'instance' => array(
            'alias' => array(
                'doctrine-container' => 'SpiffyDoctrine\Container',
                'em-default' => 'SpiffyDoctrine\EntityManagerFactory'
            ),
            'em-default' => array(
                'parameters' => array(
                    'name' => 'default',
                    'container' => 'doctrine-container'
                )
            ),
            'doctrine-container' => array(
                'parameters' => array(
                    'connections' => array(
                        'default' => array(
                            'evm' => 'default',
                            'dbname' => 'blitzaroo',
                            'user' => 'root',
                            'password' => '',
                            'host' => 'localhost',
                            'driver' => 'pdo_mysql'
                        )
                    ),
                    'caches' => array(
                        'default' => array(
                            'class' => 'Doctrine\Common\Cache\ArrayCache'
                        )
                    ),
                    'evms' => array(
                        'default' => array(
                            'class' => 'Doctrine\Common\EventManager',
                            'subscribers' => array()
                        )
                    ),
                    'ems' => array(
                        'default' => array(
                            'cache' => array(
                                'metadata' => 'default',
                                'query' => 'default',
                                'result' => 'default'
                            ),
                            'connection' => 'default',
                            'logger' => null,
                            'proxy' => array(
                                'generate' => true,
                                'dir' => __DIR__ . '/../src/Proxy',
                                'namespace' => 'SpiffyDoctrine\Proxy'
                            ),
                            'metadata' => array(
                                'registry' => array(
                                    'files' => array(
                                        __DIR__ . '/../lib/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
                                    ),
                                    'namespaces' => array()
                                ),
                                'driver' => array(
                                    'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                                    'paths' => array(),
                                    'reader' => array(
                                        'class' => 'Doctrine\Common\Annotations\AnnotationReader',
                                        'aliases' => array()
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);