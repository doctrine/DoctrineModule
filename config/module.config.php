<?php
return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                // Services
                'doctrine_service' => 'DoctrineModule\Service\Service',

                // Caching
                'doctrine_memcache'       => 'Memcache',
                'doctrine_cache_apc'      => 'Doctrine\Common\Cache\ApcCache',
                'doctrine_cache_array'    => 'Doctrine\Common\Cache\ArrayCache',
                'doctrine_cache_memcache' => 'Doctrine\Common\Cache\MemcacheCache',
            ),

            // Defaults for CLI
            'Symfony\Component\Console\Application' => array(              
                'parameters' => array(
                    //'name' => 'DoctrineModule Command Line Interface',
                    'version' => 'dev-master',
                    'helperSet' => 'Symfony\Component\Console\Helper\HelperSet',
                ),
            ),
            'Symfony\Component\Console\Helper\HelperSet' => array(               
                'parameters' => array(
                    'helpers' => array(),
                ),
            ),

            // Defaults for memcache
            'doctrine_memcache' => array(
                'parameters' => array(
                    'host' => '127.0.0.1',
                    'port' => '11211',
                ),
            ),
            'doctrine_cache_memcache' => array(
                'parameters' => array(
                    'memcache' => 'doctrine_memcache',
                ),
            ),
        ),

        // Definitions (enforcing DIC behavior)
        'definition' => array(
            'class' => array(

                // Enforcing Memcache to behave correctly (methods are not always discovered correctly by DIC)
                'Memcache' => array(
                    'addServer' => array(
                        'host' => array(
                            'type' => false,
                            'required' => true,
                        ),
                        'port' => array(
                            'type' => false,
                            'required' => true,
                        ),
                    ),
                ),

                // CLI Application setup
                'Symfony\Component\Console\Application' => array(
                    'add' => array(
                        'command' => array(
                            'type' => 'Symfony\Component\Console\Command\Command',
                            'required' => true,
                        ),
                    ),
                ),
                                
                'Symfony\Component\Console\Helper\HelperSet' => array(                   
                    'set' => array(
                        'helper' => array(
                            'type' => 'Symfony\Component\Console\Helper\HelperInterface',
                            'required' => true,
                        ),
                        'alias' => array(
                            'type' => false,
                            'required' => false,
                        ),
                    ),
                ),
                
                'DoctrineModule\Authentication\Adapter\DoctrineObject' => array(
                    'methods' => array(
                        '__construct' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'identityClassName' => array('type' => false, 'required' => true),
                            'identityProperty' => array('type' => false, 'required' => true),
                            'credentialProperty' => array('type' => false, 'required' => true),
                            'credentialCallable' => array('type' => false, 'required' => false)
                        ),                         
                        'setIdentityClassName' => array(
                            'identityClassName' => array('type' => false, 'required' => false)
                        )
                    )
                ),
                
                'DoctrineModule\Factory\Find' => array(
                    'instantiator' => array(
                        'DoctrineModule\Factory\Find', 'get',                      
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),
                            'id' => array('type' => false, 'required' => true),                            
                        ),                                             
                    ),
                ),  
                'DoctrineModule\Factory\FindAll' => array(
                    'instantiator' => array(
                        'DoctrineModule\Factory\FindAll', 'get'                        
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),                         
                        ),                                                
                    ),
                ),                 
                'DoctrineModule\Factory\FindBy' => array(
                    'instantiator' => array(
                        'DoctrineModule\Factory\FindBy', 'get'                        
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),
                            'criteria' => array('type' => false, 'required' => false),  
                            'orderBy' => array('type' => false, 'required' => false),    
                            'limit' => array('type' => false, 'required' => false),
                            'offset' => array('type' => false, 'required' => false),                                                   
                        ),                                                
                    ),
                ), 
                'DoctrineModule\Factory\FindOneBy' => array(
                    'instantiator' => array(
                        'DoctrineModule\Factory\FindOneBy', 'get'                        
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),
                            'criteria' => array('type' => false, 'required' => false),                                                 
                        ),                                                
                    ),
                ),                
            ),
        ),
    ),
);