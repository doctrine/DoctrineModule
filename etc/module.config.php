<?php

$production = array(
    'di' => array(
        'definition' => array(
            'SpiffyDoctrine\Container' => array(
                'methods' => array(
                    '__construct' => array(
                        'type' => 'array',
                        'required' => true
                    )
                )
            )
        ),
        'instance' => array(
            'alias' => array(
                'doctrine' => 'SpiffyDoctrine\Container',
            ),
            'doctrine' => array(
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
                                'generate' => false,
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

$staging = $production;
$testing = $production;
$development = $production;

$testing['di']['instance']['doctrine']['parameters']['em']['default']['proxy']['generate'] = true;
$development['di']['instance']['doctrine']['parameters']['em']['default']['proxy']['generate'] = true;

$config = compact('production', 'staging', 'testing', 'development');
return $config;
