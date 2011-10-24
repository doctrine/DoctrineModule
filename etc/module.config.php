<?php

return array(
    'di' => array(
        'definition' => array(
            'SpiffyDoctrine\EntityManagerFactory' => array(
                'instantiator' => array('SpiffyDoctrine\EntityManagerFactory', 'getInstance'),
                'methods' => array(
                    'getInstance' => array(
                        'name' => array(
                            'type' => 'array',
                            'required' => true
                        )
                    ),
                    'setContainer' => array(
                        'container' => array(
                            'type' => 'Container',
                            'required' => true
                        )
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
                    'name' => 'default'
                )
            ),
            'doctrine-container' => array(
                'parameters' => array(
                    'connection' => array(
                        'default' => array(
                            'evm' => 'default',
                            'dbname' => 'blitzaroo',
                            'user' => 'root',
                            'password' => '',
                            'host' => 'localhost',
                            'driver' => 'pdo_mysql'
                        )
                    ),
                    'cache' => array(
                        'default' => array(
                            'class' => 'Doctrine\Common\Cache\ArrayCache'
                        )
                    ),
                    'evm' => array(
                        'default' => array(
                            'class' => 'Doctrine\Common\EventManager',
                            'subscribers' => array()
                        )
                    ),
                    'em' => array(
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