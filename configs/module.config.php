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
                // temporary - should be removable once Ralph fixes a DI issue
                'SpiffyDoctrine\ORM\Configuration' => array(
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
                        'addNumericFunction' => array(
                            'numericFunction' => array(
                                'type' => 'SpiffyDoctrine\ORM\Configuration\CustomNumericFunction',
                                'required' => true,
                            ),
                        ),
                        'addStringFunction' => array(
                            'stringFunction' => array(
                                'type' => 'SpiffyDoctrine\ORM\Configuration\CustomStringFunction',
                                'required' => true,
                            ),
                        ),
                        'addDatetimeFunction' => array(
                            'datetimeFunction' => array(
                                'type' => 'SpiffyDoctrine\ORM\Configuration\CustomDatetimeFunction',
                                'required' => true,
                            ),
                        ),
                        'addHydrationMode' => array(
                            'hydrationMode' => array(
                                'type' => 'SpiffyDoctrine\ORM\Configuration\CustomHydrationMode',
                                'required' => true,
                            ),
                        ),
                        'addQuery' => array(
                            'namedQuery' => array(
                                'type' => 'SpiffyDoctrine\ORM\Configuration\NamedQuery',
                                'required' => true,
                            ),
                        ),
                        'addNativeQuery' => array(
                            'namedNativeQuery' => array(
                                'type' => 'SpiffyDoctrine\ORM\Configuration\NamedNativeQuery',
                                'required' => true,
                            ),
                        ),
                        'setAutoGenerateProxyClasses' => array(
                            'autoGenerateProxyClasses' => array(
                                'type' => false,
                                'required' => true
                            )
                        )
                    ),
                ),
                'SpiffyDoctrine\ORM\Mapping\Driver\DriverChain' => array(
                    'methods' => array(
                        'addDriver' => array(
                            'nestedDriver' => array(
                                'type' => 'Doctrine\ORM\Mapping\Driver\Driver',
                                'required' => true,
                            )
                        )
                    ),
                ),
            ),
        ),
        'instance' => array(
            'alias' => array(
                //entity manager
                'spiffy-em'                 => 'Doctrine\ORM\EntityManager',
                
                //connection
                'spiffy-connection'         => 'Doctrine\DBAL\Connection',
                
                //config
                'spiffy-configuration'      => 'SpiffyDoctrine\ORM\Configuration',
                'spiffy-metadatacache'      => 'Doctrine\Common\Cache\ArrayCache',
                'spiffy-querycache'         => 'Doctrine\Common\Cache\ArrayCache',
                'spiffy-resultcache'        => 'Doctrine\Common\Cache\ArrayCache',
                
                //event manager
                'spiffy-eventmanager'       => 'Doctrine\Common\EventManager',
                
                //metadata
                'spiffy-metadatadriver'     => 'SpiffyDoctrine\ORM\Mapping\Driver\DriverChain',
                'spiffy-annotationdriver'   => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'spiffy-cachedreader'       => 'Doctrine\Common\Annotations\CachedReader',
                'spiffy-annotationcache'    => 'Doctrine\Common\Cache\ArrayCache',
                'spiffy-indexedreader'      => 'Doctrine\Common\Annotations\IndexedReader',
                'spiffy-annotationreader'   => 'Doctrine\Common\Annotations\AnnotationReader',
            ),
            
            //entitymanager
            'spiffy-em' => array(
                'parameters' => array(
                    'conn' => 'spiffy-connection',
                    'config' => 'spiffy-configuration',
                    'eventManager' => 'spiffy-eventmanager',
                ),
            ),
            
            //connection
            'spiffy-connection' => array(
                'parameters' => array(
                    'params' => array(
                        //'driver'   => 'pdo_mysql',
                        //'host'     => 'localhost',
                        //'port'     => '3306', 
                        //'user'     => 'testuser',
                        //'password' => 'testpassword',
                        //'dbname'   => 'testdbname',
                    ),
                    'config' => 'spiffy-configuration',
                    'eventManager' => 'spiffy-eventmanager'
                ),
            ),
            
            // orm configuration
            'spiffy-configuration' => array(
                'parameters' => array(
                    'dir'                      => __DIR__ . '/../src/Proxy',
                    'ns'                       => 'SpiffyDoctrine\Proxy',
                    'driverImpl'               => 'spiffy-metadatadriver',
                    //'logger'                   => 'sqllogger', //if needed
                    'metadataCacheImpl'        => 'spiffy-metadatacache',
                    'queryCacheImpl'           => 'spiffy-querycache',
                    'resultCacheImpl'          => 'spiffy-resultcache',
                    'autoGenerateProxyClasses' => true,
                ),
            ),
            'spiffy-metadatacache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_metadatacache'
                ),
            ),
            'spiffy-querycache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_querycache'
                ),
            ),
            'spiffy-resultcache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_resultcache'
                ),
            ),
            
            //eventmanager
            'spiffy-eventmanager' => array(
                'injections' => array(
                    //just an example of how multiple events can be attached in DIC
                    //'Gedmo/Sluggable/SluggableListener
                    //'Doctrine\ORM\Event\EntityEventDelegator',
                ),
            ),
            
            //metadata
            'spiffy-metadatadriver' => array(
                'injections' => array(
                    'spiffy-annotationdriver',
                ),
            ),
            'spiffy-annotationdriver' => array(
                'parameters' => array(
                    'reader' => 'spiffy-cachedreader',
                    'paths' => array(
                        
                    ),
                ),
            ),
            'spiffy-cachedreader' => array(
                'parameters' => array(
                    'reader' => 'spiffy-indexedreader',
                    'cache' => 'spiffy-annotationcache'
                ),
            ),
            'spiffy-annotationcache' => array(
                'parameters' => array(
                    'namespace' => 'spiffy_annotation'
                ),
            ),
            'spiffy-indexedreader' => array(
                'parameters' => array(
                    'reader' => 'spiffy-annotationreader'
                ),
            ),
            
        ),
    ),
);