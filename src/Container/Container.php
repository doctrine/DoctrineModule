<?php
namespace SpiffyDoctrine\Container;

use Closure,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\EventManager,
    Doctrine\DBAL\DriverManager,
    Doctrine\ORM\Mapping\Driver\AnnotationDriver,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager,
    ReflectionClass;

class Container
{
    const ANNOTATION_DRIVER = 'Doctrine\ORM\Mapping\Driver\AnnotationDriver';
    const DEFAULT_KEY = 'default';

    /**
     * Cache instances.
     * @var array
     */
    protected $_caches = array();

    /**
     * Connection instances.
     * @var array
     */
    protected $_connections = array();
    
    /**
    * EventManager instances.
    * @var array
    */
    protected $_eventManagers = array();

    /**
     * EntityManager instances.
     * @var array
     */
    protected $_entityManagers = array();
    
    /**
     * Cache configuration.
     * @var array
     */
    protected $_cache = array();
    
    /**
     * EventManager configuration.
     * @var array
     */
    protected $_evm = array();
    
    /**
     * DBAL configuration.
     * @var array
     */
    protected $_connection = array();
    
    /**
     * EntityManager configuration.
     * @var array
     */
    protected $_em = array();
    
    /**
     * Constructor.
     * 
     * @param array $evm
     * @param array $connection
     * @param array $em
     * @param array $cache
     */
    public function __construct(array $evm, array $connection, array $em, array $cache)
    {
        $this->_evm = $evm;
        $this->_connection = $connection;
        $this->_em = $em;
        $this->_cache = $cache;
    }

    /**
     * Get a cache instance.
     * 
     * @param string $name
     * @return Doctrine\Common\Cache\AbstractCache
     */
    public function getCache($name = self::DEFAULT_KEY)
    {
        if (!isset($this->_caches[$name])) {
            $this->_prepareCache($name);
        }
        return $this->_caches[$name];
    }

    /**
     * Get a connection instance.
     *
     * @param string $name
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection($name = self::DEFAULT_KEY)
    {
        if (!isset($this->_connections[$name])) {
            $this->_prepareConnection($name);
        }
        return $this->_connections[$name];
    }
    
    /**
    * Get an event manager instance.
    *
    * @param string $name
    * @return Doctrine\Common\EventManager
    */
    public function getEventManager($name = self::DEFAULT_KEY)
    {
        if (!isset($this->_eventManagers[$name])) {
            $this->_prepareEventManager($name);
        }
        return $this->_eventManagers[$name];
    }

    /**
     * Get an entity manager instance.
     * 
     * @param string $name
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager($name = self::DEFAULT_KEY)
    {
        if (!isset($this->_entityManagers[$name])) {
            $this->_prepareEntityManager($name);
        }
        return $this->_entityManagers[$name];
    }
    
    /**
     * Prepares a cache instance.
     * 
     * @todo add additional parameters for configuring memcache.
     * @param string $name
     */
    protected function _prepareCache($name)
    {
        if (!isset($this->_cache[$name])) {
            throw new Exception\CacheNotFound($name);
        }

        $cache = new $this->_cache[$name]['class'];

        // put memcache options here

        $this->_caches[$name] = $cache;
    }

    /**
     * Prepares a connecton instance.
     * 
     * @param string $name
     */
    protected function _prepareConnection($name)
    {
        if (!isset($this->_connection[$name])) {
            throw new Exception\ConnectionNotFound($name);
        }

        $this->_connections[$name] = DriverManager::getConnection(
            $this->_connection[$name],
            null,
            $this->getEventManager($this->_connection[$name]['evm'])
        );
    }
    
    /**
     * Prepares an eveent manager instance.
     * 
     * @todo: Add event listeners?
     * @param string $name
     */
    protected function _prepareEventManager($name)
    {
        if (!isset($this->_evm[$name])) {
            throw new Exception\EventManagerNotFound($name);
        }
        
        $evm = new EventManager();
        
        // todo: put listeners here?
        
        // subscribers
        if (isset($this->_evm[$name]['subscribers']) && is_array($this->_evm[$name]['subscribers'])) {
            foreach($this->_evm[$name]['subscribers'] as $subscriber) {
                $instance = new $subscriber();
                $evm->addEventSubscriber($instance);
            }
        }
        
        $this->_eventManagers[$name] = $evm;
    }

    /**
     * Prepares an entity manager instance.
     * 
     * @param string $name
     */
    protected function _prepareEntityManager($name)
    {
        if (!isset($this->_em[$name])) {
            throw new Exception\EntityManagerNotFound($name);
        }

        $opts = $this->_em[$name];
        $connection = isset($opts['connection']) ? $opts['connection'] : self::DEFAULT_KEY;

        $driver = $this->_createDriver($opts['driver']);
        $this->_registerAnnotations($opts['registry']);

        $config = new Configuration();
        $config->setProxyDir($opts['proxy']['dir']);
        $config->setProxyNamespace($opts['proxy']['namespace']);
        $config->setAutoGenerateProxyClasses($opts['proxy']['generate']);
        $config->setMetadataDriverImpl($driver);
        
        $config->setMetadataCacheImpl($this->getCache($opts['cache']['metadata']));
        $config->setQueryCacheImpl($this->getCache($opts['cache']['query']));
        $config->setResultCacheImpl($this->getCache($opts['cache']['result']));

        $em = EntityManager::create($this->getConnection($connection), $config);
        
        if (isset($opts['logger'])) {
            $dbalConfig = $em->getConnection()->getConfiguration();
            
            $logger = new $opts['logger']();
            $dbalConfig->setSqlLogger($logger);
        }

        $this->_entityManagers[$name] = $em;
    }

    /**
     * Creates the metadata driver.
     * 
     * @param array $opts
     * 
     * @return mixed
     */
    protected function _createDriver(array $opts)
    {
        $driverClass = $opts['class'];

        $refl = new ReflectionClass($driverClass);

        // annotation driver has extra initialization options
        if ($refl->getName() == self::ANNOTATION_DRIVER || $refl->isSubclassOf(self::ANNOTATION_DRIVER)) {
            if (!isset($opts['reader']['class'])) {
                throw new Exception\InvalidAnnotationReaderClass;
            }

            $readerClass = $opts['reader']['class'];
            $reader = new $readerClass();

            return new $driverClass($reader, $opts['paths']);
        }
        
        return new $driverClass($opts['paths']);
    }
    
    /**
     * Registers annotations using both namespace and file formats.
     * 
     * @param array $opts
     */
    protected function _registerAnnotations(array $opts)
    {
        if (isset($opts)) {
            // files
            if (isset($opts['files'])) {
                if (!is_array($opts['files'])) {
                    $opts['files'] = array($opts['files']);
                }

                foreach ($opts['files'] as $file) {
                    AnnotationRegistry::registerFile($file);
                }
            }
            
            // namespaces
            if (isset($opts['namespaces'])) {
                if (!is_array($opts['namespaces'])) {
                    $opts['namespaces'] = array($opts['namespaces']);
                }

                AnnotationRegistry::registerAutoloadNamespaces($opts['namespaces']);
            }
        }
    }
}
