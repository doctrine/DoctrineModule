<?php
return array(
    'di' => array(
        'definition' => array(
            'class' => array(
                'SpiffyDoctrine\Service\Doctrine' => array(
                    'methods' => array(
                        '__construct' => array(
                            'conn'   => array('type' => false, 'required' => true),
                            'config' => array('type' => false, 'required' => true),
                            'evm'    => array('type' => false, 'required' => false) 
                        )
                    )
                )
            ),
        ),
        'instance' => array(
            'alias' => array(
                'doctrine' => 'SpiffyDoctrine\Service\Doctrine',
            ),
            'doctrine' => array(
                'parameters' => array(
                    'conn' => array(
                    ),
                    'config' => array(
                        'auto-generate-proxies'     => true,
                        // @todo: figure out how to de-couple the Proxy dir
                        'proxy-dir'                 => true,
                        'proxy-namespace'           => 'SpiffyDoctrine\Proxy',
                        'metadata-driver-impl'      => array(
                            // array('className', 'namespace')
                        ),
                        'metadata-cache-impl'       => 'Doctrine\Common\Cache\ArrayCache',
                        'query-cach-impl'           => 'Doctrine\Common\Cache\ArrayCache',
                        'result-cache-impl'         => 'Doctrine\Common\Cache\ArrayCache',
                        'custom-datetime-functions' => array(
                            // array('name', 'className')
                        ),
                        'custom-numeric-functions'  => array(
                        ),
                        'custom-string-functions'   => array(
                        ),
                        'custom-hydration-modes'    => array(
                            // array('modeName', 'hydrator')
                        ),
                        'named-queries'             => array(
                            // array('name', 'dql')
                        ),
                        'named-native-queries'      => array(
                            // array('name', 'sql', 'rsm')
                        ),
                        'sql-logger'                => null,
                    ),
                    'evm' => array(
                    )  
                )
            )
        )
    )
);