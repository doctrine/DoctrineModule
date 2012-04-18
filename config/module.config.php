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

                // CLI tools
                'doctrine_cli' => 'Symfony\Component\Console\Application',
                'doctrine_cli_helperset' => 'Symfony\Component\Console\Helper\HelperSet',
            ),

            // Defaults for CLI
            'doctrine_cli' => array(
                'parameters' => array(
                    'name' => 'DoctrineModule Command Line Interface',
                    'version' => 'dev-master',
                ),
                'injections' => array(
                    'doctrine_cli_helperset',
                ),
            ),
            'doctrine_cli_helperset' => array(
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
                    'methods' => array(
                        'add' => array(
                            'command' => array(
                                'type' => 'Symfony\Component\Console\Command\Command',
                                'required' => true,
                            ),
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
            ),
        ),
    ),
);