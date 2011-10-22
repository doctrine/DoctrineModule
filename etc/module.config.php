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
                    'config' => array(
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
                        'dbal' => array(
                            'connection' => array(
                                'default' => array(
                                    'evm' => 'default',
                                    'dbname' => 'blitzaroo',
                                    'user' => 'root',
                                    'password' => '',
                                    'host' => 'localhost',
                                    'driver' => 'pdo_mysql'
                                )
                            )
                        ),
                        'orm' => array(
                            'em' => array(
                                'default' => array(
                                    'connection' => 'default',
                                    'proxy' => array(
                                        'generate' => false,
                                        'dir' => __DIR__ . '/../src/Proxy',
                                        'namespace' => 'SpiffyDoctrine\Proxy'
                                    ),
                                    'cache' => array(
                                        'metadata' => 'default',
                                        'query' => 'default',
                                        'result' => 'default'
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
        )
    )
);

$staging = $production;
$testing = $production;
$development = $production;

$testing['spiffy_doctrine']['orm']['em']['default']['proxy']['generate'] = true;
$development['spiffy_doctrine']['orm']['em']['default']['proxy']['generate'] = true;

$config = compact('production', 'staging', 'testing', 'development');
return $config;
