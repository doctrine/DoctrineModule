<?php
return array(
    'di' => array(
        'definition' => array(
            'class' => array(
                'Memcache' => array(
                    'addServer' => array(
                        'host' => array('type' => false, 'required' => true),
                        'port' => array('type' => false, 'required' => true),
                    )
                ),
            ),
        ),
        'instance' => array(
            'alias' => array(
                // caching
                'doctrine_memcache'       => 'Memcache',
                'doctrine_cache_apc'      => 'Doctrine\Common\Cache\ApcCache',
                'doctrine_cache_array'    => 'Doctrine\Common\Cache\ArrayCache',
                'doctrine_cache_memcache' => 'Doctrine\Common\Cache\MemcacheCache',
            ),
            'doctrine_memcache' => array(
                'parameters' => array(
                    'host' => '127.0.0.1',
                    'port' => '11211'
                )
            ),
            'doctrine_cache_memcache' => array(
                'parameters' => array(
                    'memcache' => 'doctrine_memcache' 
                )
            ),
        )
    )
);