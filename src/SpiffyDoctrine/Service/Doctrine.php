<?php
namespace SpiffyDoctrine\Service;
use Doctrine\Common,
    Doctrine\DBAL,
    Doctrine\ORM;

class Doctrine
{
    /**
     * Definition for configuration options. 
     * 
     * @var array
     */
    protected $_configurationDefinition = array(
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
     * Definition for custom functions.
     * 
     * @var array
     */
    protected $_customFunctionDefinition = array(
        'required' => array(
            'name'      => 'string',
            'className' => 'string'
        )
    );
    
    /**
     * Definition for custom hydrators.
     * 
     * @var array
     */
    protected $_customHydratorDefinition = array(
        'required' => array(
            'modeName' => 'string',
            'hydrator' => 'string'
        )
    );
    
    /**
     * Definition for named DQL queries.
     * 
     * @var array
     */
    protected $_namedQueryDefinition = array(
        'required' => array(
            'name' => 'string',
            'dql'  => 'string'
        )
    );
    
    /**
     * Definition for named native queries.
     * 
     * @var array
     */
    protected $_namedNativeQueryDefinition = array(
        'required' => array(
            'name' => 'string',
            'sql'  => 'string',
            'rsm'  => 'string'
        )
    );
    
    /**
     * Definition for driver chain options.
     * 
     * @var array
     */
    protected $_driverChainDefinition = array(
        'required' => array(
            'class' => 'string',
            'namespace' => 'string',
            'paths' => 'array',
        )
    );
    
    /**
     * Definition for annotation drivers. Annotation drivers have 
     * additional option requirements before being added to the 
     * driver chain.
     * 
     * @var array
     */
    protected $_annotationDriverDefinition = array(
        'required' => array(
            'cache_class' => 'string'
        )
    );
    
    /**
     * Definition for event manager options.
     */
    protected $_eventManagerDefinition = array(
        'optional' => array(
            'subscribers' => 'array'
        )
    );
        
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;
    
    /**
     * @var Doctrine\ORM\Mapping\Driver\DriverChain
     */
    protected $_driverChain;
    
    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $_conn;
    
    /**
     * @var Doctrine\ORM\Configuration
     */
    protected $_config;
    
    /**
     * @var Doctrine\Common\EventManager
     */
    protected $_evm;

    /**    
     * Constructor.
     * 
     * @param array $conn   Connection options.
     * @param array $config Configuration options, @see $_configurationDefinition
     * @param array $evm    EventManager options, @see $_eventManagerDefinition
     * @param PDO   $pdo    PDO instance, if needed. This is for Zend\Di support. You can also
     *                      pass an instance of PDO to $conn['pdo'].
     * @return void 
     */
    public function __construct(array $conn, array $config, array $evm = null, \PDO $pdo = null)
    {
        if ($pdo) {
            $conn['pdo'] = $pdo;
        }
        
        $this->_createConfiguration($config);
        $this->_createEventManager($evm);
        $this->_createConnection($conn);
        $this->_createEntityManager();
    }
    
    /**
     * Get entity manager.
     * 
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }
    
    /**
     * Get event manager.
     * 
     * @return Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->_evm;
    }
    
    /**
     * Get connection.
     * 
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->_conn;
    }
    
    /**
     * Get configuration.
     * 
     * @return Doctrine\ORM\Configuration
     */
    public function getConfiguration()
    {
        return $this->_config;
    }
    
    /**
     * Get driver chain.
     * 
     * @return Doctrine\ORM\Mapping\Driver\DriverChain
     */
    public function getDriverChain()
    {
        return $this->_driverChain;
    }
    
    /**
     * Creates the EntityManager from a pre-configured connection, configuration,
     * and event manager present in $_conn, $_config, and $_evm respectively.
     * 
     * @return void
     */
    protected function _createEntityManager()
    {
        $this->_em = ORM\EntityManager::create($this->_conn, $this->_config, $this->_evm);
    }
    
    /**
     * Creates a connection using the DBAL\DriverManager and a pre-configured
     * Configuration and EventManager. This method assumes the configuration
     * and event manager have been setup and are present in $_config and $_evm.
     * 
     * @param array $opts
     * @return void
     */
    protected function _createConnection(array $opts)
    {
        $this->_conn = DBAL\DriverManager::getConnection(
            $opts,
            $this->_config,
            $this->_evm
        );
    }
    
    /**
     * Creates a Doctrine\ORM\Configuration from an array of options.
     * 
     * @param array $opts
     * @return void
     */
    protected function _createConfiguration(array $opts) 
    {
        $this->_validateOptions($opts, $this->_configurationDefinition);
        
        $config = new ORM\Configuration;
        $config->setAutoGenerateProxyClasses($opts['auto_generate_proxies']);
        $config->setProxyDir($opts['proxy_dir']);
        $config->setProxyNamespace($opts['proxy_namespace']);
        
        // add custom functions
        foreach($opts['custom_datetime_functions'] as $function) {
            $this->_validateOptions($function, $this->_customFunctionDefinition);
            $config->addCustomDatetimeFunction($function['name'], $function['className']);
        }
        
        foreach($opts['custom_string_functions'] as $function) {
            $this->_validateOptions($function, $this->_customFunctionDefinition);
            $config->addCustomStringFunction($function['name'], $function['className']);
        }
        
        foreach($opts['custom_numeric_functions'] as $function) {
            $this->_validateOptions($function, $this->_customFunctionDefinition);
            $config->customNumericFunctions($function['name'], $function['className']);
        }
        
        foreach($opts['named_queries'] as $query) {
            $this->_validateOptions($query, $this->_namedQueryDefinition);
            $config->customNumericFunctions($query['name'], $query['dql']);
        }
        
        foreach($opts['named_native_queries'] as $query) {
            $this->_validateOptions($query, $this->_namedNativeQueryDefinition);
            $config->customNumericFunctions($query['name'], $query['sql'], new $query['rsm']);
        }

        // logger
        if ($opts['sql_logger']) {
            $config->setSQLLogger(new $opts['sql_logger']);
        }
        
        // create metadata driver chain
        $this->_createDriverChain($opts['metadata_driver_impl']);
        $config->setMetadataDriverImpl($this->_driverChain);
        
        $this->_config = $config;
    }
    
    /**
     * Creates an event manager based on passed parameters.
     * 
     * @param array $opts
     * @return void
     */
    protected function _createEventManager(array $opts)
    {
        $this->_validateOptions($opts, $this->_eventManagerDefinition);
        
        $evm = new Common\EventManager;
        foreach($opts['subscribers'] as $subscriber) {
            if (is_string($subscriber)) {
                if (!class_exists($subscriber)) {
                    throw new \InvalidArgumentException(sprintf(
                       'failed to register subscriber "%s" because the class does not exist.',
                       $subscriber 
                    ));
                }
                $subscriber = new $subscriber;
            }
            
            $evm->addEventSubscriber($subscriber);
        }
        
        $this->_evm = $evm;
    }
    
    /**
     * Creates a driver chain based on passed parameters. Drivers should, at minimum, 
     * specify the class, namespace, and paths. AnnotationDrivers have two additional
     * options 'cache_class' that are required.
     * 
     * @todo allow setting own driver chain extended from ORM\Mapping\Driver\DriverChain
     * @param array $drivers
     * @return void
     */
    protected function _createDriverChain(array $drivers)
    {
        $chain = new ORM\Mapping\Driver\DriverChain();
        foreach($drivers as $opts) {
            $this->_validateOptions($opts, $this->_driverChainDefinition);
            
            // use reflection only if necessary
            $isAnnotation = false;
            if ($opts['class'] == 'Doctrine\ORM\Mapping\Driver\AnnotationDriver') {
                $isAnnotation = true;
            } else {
                $reflClass = new \ReflectionClass($opts['class']);
                $isAnnotation = $reflClass->isSubclassOf('Doctrine\ORM\Mapping\Driver\AnnotationDriver');
            }
            
            // annotation drivers have extra special options
            if ($isAnnotation) {       
                $this->_validateOptions($opts, $this->_annotationDriverDefinition);
                
                $cache = new $opts['cache_class'];
                $annotationReader = new Common\Annotations\AnnotationReader;
                $indexedReader = new Common\Annotations\IndexedReader($annotationReader);
                $cachedReader = new Common\Annotations\CachedReader($indexedReader, $cache);
                
                $driver = new $opts['class']($cachedReader, $opts['paths']);
            } else {
                $driver = new $opts['class']($opts['paths']);
            }
            $chain->addDriver($driver, $opts['namespace']);
        }

        $this->_driverChain = $chain; 
    }
    
    /**
     * Validates that required options are present and of the correct type and generates
     * optional options of the correct type if missing.
     * 
     * @param array $opts Options to check.
     * @param array $defs Definition to check options against in the form:
     *                  'required' => array('var' => 'type'),
     *                  'optional' => array('var' => 'type')
     * @throws InvalidArgumentException on missing required arguments.
     * @throws InvalidArgumentException when arguments are of the wrong type.
     * @return void
     */
    protected function _validateOptions(array &$opts, array $defs)
    {
        if (isset($defs['required']) && is_array($defs['required'])) {
            // validate and ensure required options are of the correct type
            foreach($defs['required'] as $var => $type) {
                if (!isset($opts[$var])) {
                    throw new \InvalidArgumentException(sprintf(
                        'missing option: "%s" is a required parameter.',
                        $var
                    ));
                }
                
                // if class_exists of $type then instantiate new object
                if (null !== $type) {
                    $got = gettype($opts[$var]);
                    if ($got !== $type) {
                        throw new \InvalidArgumentException(sprintf(
                            'invalid option: "%s" should be a %s, got %s.',
                            $var,
                            $type,
                            $got
                        ));
                    }
                }
            }
        }

        if (isset($defs['optional']) && is_array($defs['optional'])) {
            // fill in missing optional arguments
            foreach($defs['optional'] as $var => $type) {
                if (!isset($opts[$var]) || !gettype($opts[$var]) == $type) {
                    settype($opts[$var], $type);
                }
            }
        }
    }
}
