<?php

$production = array(
    'doctrine' => array(
        'doctrine_path' => __DIR__ . '/../lib/',
        'register_default_annotations' => true,
    ),
    'di' => array(
        'definition' => array(
            'class' => array(
                'Doctrine\ORM\EntityManager' => array(
                    'instantiator' => array('Doctrine\ORM\EntityManager', 'create'),
                    'methods' => array(
                        'create' => array(
                            'conn'   => array(
                                'type' => null, 
                                'required' => true
                            ),
                            'config' => array(
                                'type' => 'Doctrine\ORM\Configuration', 
                                'required' => true
                            ),
                            'evm' => array(
                                'type' => 'Doctrine\Common\EventManager', 
                                'required' => false
                            )
                        ),
                    )
                )
            )
        ),
        'instance' => array(
            'alias' => array(
                'doctrine-config' => 'Doctrine\ORM\Configuration',
                'doctrine-em'     => 'Doctrine\ORM\EntityManager',
                'doctrine-evm'    => 'Doctrine\Common\EventManager'
            ),
            'doctrin-evm' => array(
                'parameters' => array()
            ),
            'doctrine-config' => array(
                'parameters' => array(
                    'ns'         => 'Doctrine\Proxy',
                    'dir'        => realpath(__DIR__ . '/../src/Doctrine/Proxy'),
                    'bool'       => false,
                    'cacheImpl'  => 'Doctrine\Common\Cache\ArrayCache',
                    'driverImpl' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver'
                )
            ),
            'doctrine-em' => array(
                'parameters' => array(
                    'conn' => array(
                        'driver'   => 'pdo_mysql',
                        'host'     => 'localhost',
                        'user'     => 'doctrine',
                        'password' => 'doctrine'
                    ),
                    'config' => 'doctrine-config',
                    'evm'    => 'doctrine-evm'
                )
            ),
            'Doctrine\ORM\Mapping\Driver\AnnotationDriver' => array(
                'parameters' => array(
                    'reader' => 'Doctrine\Common\Annotations\AnnotationReader',
                    'paths'  => array()
                )
            )
        )
    )
);

$staging = $production;
$testing = $production;
$development = $production;

$testing['di']['instance']['Doctrine\ORM\Configuration']['parameters']['bool'] = true;
$development['di']['instance']['Doctrine\ORM\Configuration']['parameters']['bool'] = true;

$config = compact('production', 'staging', 'testing', 'development');
return $config;
