<?php
namespace SpiffyDoctrine\Instance;
use Doctrine\ORM\Configuration as DoctrineConfiguration,
    Doctrine\ORM\Mapping\Driver\DriverChain;

class Cache extends Instance
{
    /**
     * Definition for configuration options. 
     * 
     * @var array
     */
    protected $definition = array(
        'required' => array(
            'auto_generate_proxies' => 'boolean',
            'proxy_dir'             => 'string',
            'proxy_namespace'       => 'string',
            'metadata_driver_impl'  => 'array',
            'metadata_cache_impl'   => 'string',
            'query_cache_impl'      => 'string',
        ),
        'optional' => array(
            'result_cache_impl'         => 'null',
            'custom_datetime_functions' => 'array',
            'custom_numeric_functions'  => 'array',
            'custom_string_functions'   => 'array',
            'custom_hydration_modes'    => 'array',
            'named_queries'             => 'array',
            'named_native_queries'      => 'array',
            'sql_logger'                => 'array'
        )
    );
    
    /**
     * (non-PHPdoc)
     * @see SpiffyDoctrine\Instance.Instance::loadInstance()
     */
    protected function loadInstance()
    {
    }
}