<?php
namespace SpiffyDoctrine\Doctrine\ODM\MongoDB;
use Doctrine\Common\Cache\Cache,
    Doctrine\MongoDB\Loggable,
    Doctrine\ODM\MongoDB\Configuration as ODMConfiguration,
    SpiffyDoctrine\Doctrine\Instance;

class Configuration extends Instance
{
    /**
     * Definition for configuration options. 
     * 
     * @var array
     */
    protected $definition = array(
        'required' => array(
            'auto_generate_proxies'   => 'boolean',
            'proxy_dir'               => 'string',
            'proxy_namespace'         => 'string',
            'auto_generate_hydrators' => 'boolean',
            'hydrator_dir'            => 'string',
            'hydrator_namespace'      => 'string',
        ),
        'optional' => array(
        )
    );
    
    /**
     * @var Doctrine\ORM\Mapping\Driver\Driver
     */
    protected $metadataDriver;
    
    /**
     * @var Doctrine\Common\Cache\Cache
     */
    protected $metadataCache;
    
    /**
     * @var Doctrine\DBAL\Logging\SQLLogger
     */
    protected $logger;
    
    /**
     * Constructor.
     * 
     * @param array    $opts
     * @param Driver   $metadataDriver
     * @param Cache    $metadataCache
     * @param Loggable $logger
     */
    public function __construct(array $opts, $metadataDriver, Cache $metadataCache, Loggable $logger = null)
    {
    	if ($metadataDriver instanceof DriverChain) {
    		$metadataDriver = $metadataDriver->getInstance();
    	}
    	
    	$this->metadataDriver = $metadataDriver;
        $this->metadataCache  = $metadataCache;
        $this->logger         = $logger;
        
        parent::__construct($opts);
    }
    
    /**
     * (non-PHPdoc)
     * @see SpiffyDoctrine\Instance.Instance::loadInstance()
     */
    protected function loadInstance()
    {
        $opts   = $this->opts;
        $config = new ODMConfiguration;
        
        // proxies
        $config->setAutoGenerateProxyClasses($opts['auto_generate_proxies']);
        $config->setProxyDir($opts['proxy_dir']);
        $config->setProxyNamespace($opts['proxy_namespace']);
        
        // hydrators
        $config->setAutoGenerateHydratorClasses($opts['auto_generate_hydrators']);
        $config->setHydratorDir($opts['hydrator_dir']);
        $config->setHydratorNamespace($opts['hydrator_namespace']);
        
        // caching
        $config->setMetadataCacheImpl($this->metadataCache);

        // logger
        $config->setLoggerCallable($this->logger);
        
        // finally, the driver
        $config->setMetadataDriverImpl($this->metadataDriver);
        
        $this->instance = $config;
    }
}