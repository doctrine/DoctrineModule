<?php
return array(
    'spiffy-doctrine' => array(
        'annotations' => array(
            'namespaces' => array(
                //'MyNamespace' => 'my/namespace/folder'
            ),
            'files' => array(
                // 'my/annotation/file.php'
            )
        )
    ),
    'di' => array(
        'definition' => array(
            'class' => array(
                'Doctrine\Common\EventManager' => array(
                    'addEventSubscriber' => array(
                        'subscriber' => array(
                            'type' => 'Doctrine\Common\EventSubscriber',
                            'required' => true
                        )
                    )
                ),
                'Doctrine\ORM\EntityManager' => array(
                    'instantiator' => array(
                        'Doctrine\ORM\EntityManager',
                        'create'
                    ),
                    'methods' => array(
                        'create' => array(
                            'conn' => array(
                                'type' => 'Doctrine\DBAL\Connection',
                                'required' => true
                            ),
                            'config' => array(
                                'type' => 'Doctrine\ORM\Configuration',
                                'required' => true
                            ),
                            'eventManager' => array(
                                'type' => 'Doctrine\Common\EventManager',
                                'required' => false
                            ),
                        ),
                    )
                ),
                'Doctrine\DBAL\DriverManager' => array(
                    'methods' => array(
                        'getConnection' => array(
                            'params' => array(
                                'type' => false,
                                'required' => true,
                            ),
                            'config' => array(
                                'type' => 'Doctrine\DBAL\Configuration',
                                'required' => false,
                            ),
                            'eventManager' => array(
                                'type' => 'Doctrine\Common\EventManager',
                                'required' => false,
                            ),
                        ),
                    ),
                ),
                'Doctrine\DBAL\Connection' => array(
                    'instantiator' => array(
                        'Doctrine\DBAL\DriverManager',
                        'getConnection',
                    ),
                ),
                'Doctrine\ORM\Configuration' => array(
                    'methods' => array(
                        'setMetadataCacheImpl' => array(
                            'metadataCacheImpl' => array(
                                'type' => 'Doctrine\Common\Cache\Cache',
                                'required' => true,
                            ),
                        ),
                        'setQueryCacheImpl' => array(
                            'queryCacheImpl' => array(
                                'type' => 'Doctrine\Common\Cache\Cache',
                                'required' => true,
                            ),
                        ),
                        'setResultCacheImpl' => array(
                            'resultCacheImpl' => array(
                                'type' => 'Doctrine\Common\Cache\Cache',
                                'required' => true,
                            ),
                        ),
                        'setResultCacheImpl' => array(
                            'resultCacheImpl' => array(
                                'type' => 'Doctrine\Common\Cache\Cache',
                                'required' => true,
                            ),
                        ),
                        'addCustomDatetimeFunction' => array(
                            'name' => array('type' => false, 'required' => true),
                            'className' => array('type' => false, 'required' => true)
                        ),
                        'addCustomNumericFunction' => array(
                            'name' => array('type' => false, 'required' => true),
                            'className' => array('type' => false, 'required' => true)
                        ),
                        'addCustomStringFunction' => array(
                            'name' => array('type' => false, 'required' => true),
                            'className' => array('type' => false, 'required' => true)
                        ),
                        'addCustomHydrationMode' => array(
                            'modeName' => array('type' => false, 'required' => true),
                            'hydrator' => array('type' => false, 'required' => true)
                        ),
                        'addNamedQuery' => array(
                            'name' => array('type' => false, 'required' => true),
                            'dql' => array('type' => false, 'required' => true)
                        ),
                        'addNamedNativeQuery' => array(
                            'name' => array('type' => false, 'required' => true),
                            'sql' => array('type' => false, 'required' => true),
                            'rsm' => array('type' => 'Doctrine\ORM\Query\ResultSetMapping', 'required' => true)
                        ),
                        'setAutoGenerateProxyClasses' => array(
                            'autoGenerateProxyClasses' => array(
                                'type' => false,
                                'required' => true
                            )
                        )
                    ),
                ),
                'Doctrine\ORM\Mapping\Driver\DriverChain' => array(
                    'methods' => array(
                        'addDriver' => array(
                            'nestedDriver' => array(
                                'type' => 'Doctrine\ORM\Mapping\Driver\Driver',
                                'required' => true,
                            ),
                            'namespace' => array(
                                'type' => false,
                                'required' => true
                            )
                        ),
                    )
                ),
            ),
        ),
        'instance' => array(
            'alias' => array(
                // entity manager
                'doctrine-em'                 => 'Doctrine\ORM\EntityManager',
                
                // connection
                'doctrine-connection'         => 'Doctrine\DBAL\Connection',
                
                // config
                'doctrine-configuration'      => 'Doctrine\ORM\Configuration',
                'doctrine-metadatacache'      => 'Doctrine\Common\Cache\ArrayCache',
                'doctrine-querycache'         => 'Doctrine\Common\Cache\ArrayCache',
                'doctrine-resultcache'        => 'Doctrine\Common\Cache\ArrayCache',
                
                // event manager
                'doctrine-eventmanager'       => 'Doctrine\Common\EventManager',
                
                // drivers
                'doctrine-driverchain'        => 'Doctrine\ORM\Mapping\Driver\DriverChain',
                
                'doctrine-annotationdriver'   => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'doctrine-phpdriver'          => 'Doctrine\ORM\Mapping\Driver\PHPDriver',
                'doctrine-staticphpdriver'    => 'Doctrine\ORM\Mapping\Driver\StaticPHPDriver',
                'doctrine-xmldriver'          => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'doctrine-yamldriver'         => 'Doctrine\ORM\Mapping\Driver\YamlDriver',
                
                // readers
                'doctrine-cachedreader'       => 'Doctrine\Common\Annotations\CachedReader',
                'doctrine-annotationcache'    => 'Doctrine\Common\Cache\ArrayCache',
                'doctrine-indexedreader'      => 'Doctrine\Common\Annotations\IndexedReader',
                'doctrine-annotationreader'   => 'Doctrine\Common\Annotations\AnnotationReader',
            ),
            
            'doctrine-em' => array(
                'parameters' => array(
                    'conn' => 'doctrine-connection',
                    'config' => 'doctrine-configuration',
                    'eventManager' => 'doctrine-eventmanager',
                ),
            ),
            
            'doctrine-connection' => array(
                'parameters' => array(
                    'params' => array(
                        //'driver'   => 'pdo_mysql',
                        //'host'     => 'localhost',
                        //'port'     => '3306', 
                        //'user'     => 'testuser',
                        //'password' => 'testpassword',
                        //'dbname'   => 'testdbname',
                    ),
                    'config' => 'doctrine-configuration',
                    'eventManager' => 'doctrine-eventmanager'
                ),
            ),
            
            'doctrine-configuration' => array(
                'parameters' => array(
                    'dir'                      => __DIR__ . '/../src/Proxy',
                    'ns'                       => 'SpiffyDoctrine\Proxy',
                    'driverImpl'               => 'doctrine-driverchain',
                    //'logger'                   => 'sqllogger', //if needed
                    'metadataCacheImpl'        => 'doctrine-metadatacache',
                    'queryCacheImpl'           => 'doctrine-querycache',
                    'resultCacheImpl'          => 'doctrine-resultcache',
                    'autoGenerateProxyClasses' => true,
                ),
                // shows an example of how to add custom functions/queries/hydrators
                //'injections' => array(
                //    'addCustomDatetimeFunction' => array(
                //        array('name' => 'MyFunction', 'className' => 'My\Class\Name')
                //    )
                //)
            ),
            'doctrine-metadatacache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_metadatacache'
                ),
            ),
            'doctrine-querycache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_querycache'
                ),
            ),
            'doctrine-resultcache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_resultcache'
                ),
            ),
            'doctrine-eventmanager' => array(
                'injections' => array(
                    //just an example of how multiple events can be attached in DIC
                    //'Gedmo/Sluggable/SluggableListener
                ),
            ),
            'doctrine-driverchain' => array(
                'injections' => array(
                    'addDriver' => array(
                        //array('nestedDriver' => 'doctrine-annotationdriver', 'namespace' => 'My\Annotation\Namespace'),
                        //array('nestedDriver' => 'doctrine-yamldriver', 'namespace' => 'My\Yaml\Namespace')
                    )
                ),
            ),
            'doctrine-annotationdriver' => array(
                'parameters' => array(
                    'reader' => 'doctrine-cachedreader',
                    'paths' => array(
                    ),
                ),
            ),
            'doctrine-cachedreader' => array(
                'parameters' => array(
                    'reader' => 'doctrine-indexedreader',
                    'cache' => 'doctrine-annotationcache'
                ),
            ),
            'doctrine-annotationcache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_annotation'
                ),
            ),
            'doctrine-indexedreader' => array(
                'parameters' => array(
                    'reader' => 'doctrine-annotationreader'
                ),
            ),
            
        ),
    ),
);