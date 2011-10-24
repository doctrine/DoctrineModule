<?php
namespace SpiffyDoctrine;

use Closure,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\EventManager,
    Doctrine\DBAL\DriverManager,
    Doctrine\ORM\Mapping\Driver\AnnotationDriver,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager,
    ReflectionClass;

class Container
{
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
            throw new \InvalidArgumentException(
                "Cache with name '{$name}' could not be located in configuration."
            );
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
            throw new \InvalidArgumentException(
                "Connection with name '{$name}' could not be located in configuration."
            );
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
     * @param string $name
     */
    protected function _prepareEventManager($name)
    {
        if (!isset($this->_evm[$name])) {
            throw new \InvalidArgumentException(
                "EventManager with name '{$name}' could not be located in configuration."
            );
        }
        
        $evm = new EventManager();
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
            throw new \InvalidArgumentException(
                "EntityManager with name '{$name}' could not be located in configuration."
            );
        }

        $emOptions = $this->_em[$name];
        $connection = isset($emOptions['connection']) ? $emOptions['connection'] : self::DEFAULT_KEY;

        $driverOptions = $emOptions['metadata']['driver'];
        $driverClass = $driverOptions['class'];
        $driver = null;

        $reflClass = new ReflectionClass($driverClass);

        // annotation driver has extra initialization options
        if ($reflClass->getName() == 'Doctrine\ORM\Mapping\Driver\AnnotationDriver'
            || $reflClass->isSubclassOf('Doctrine\ORM\Mapping\Driver\AnnotationDriver')) {
            if (!isset($driverOptions['reader']['class'])) {
                throw new \InvalidArgumentException(
                    'AnnotationDriver was specified but no reader options exist');
            }

            $readerClass = $driverOptions['reader']['class'];
            $reader = new $readerClass();

            $driver = new $driverClass($reader, $driverOptions['paths']);
        } else {
            $driver = new $driverClass($driverOptions['paths']);
        }

        // register annotations
        if (isset($emOptions['metadata']['registry'])) {
            $regOptions = $emOptions['metadata']['registry'];

            // files
            if (isset($regOptions['files'])) {
                if (!is_array($regOptions['files'])) {
                    $regOptions['files'] = array(
                        $regOptions['files']
                    );
                }

                // sanity check
                if (!is_array($regOptions['files'])) {
                    throw new \InvalidArgumentException(
                        'Registry files must be an array of files');
                }

                foreach ($regOptions['files'] as $file) {
                    AnnotationRegistry::registerFile($file);
                }
            }
            
            // namespaces
            if (isset($regOptions['namespaces'])) {
                if (!is_array($regOptions['namespaces'])) {
                    $regOptions['namespaces'] = array(
                        $regOptions['namespaces']
                    );
                }

                if (!is_array($regOptions['namespaces'])) {
                    throw new \InvalidArgumentException(
                        'Registry namespaces must be an array of key => value pairs'
                    );
                }

                AnnotationRegistry::registerAutoloadNamespaces($regOptions['namespaces']);
            }
        }

        $config = new Configuration();
        $config->setProxyDir($emOptions['proxy']['dir']);
        $config->setProxyNamespace($emOptions['proxy']['namespace']);
        $config->setAutoGenerateProxyClasses($emOptions['proxy']['generate']);
        $config->setMetadataDriverImpl($driver);
        
        $config->setMetadataCacheImpl($this->getCache($emOptions['cache']['metadata']));
        $config->setQueryCacheImpl($this->getCache($emOptions['cache']['query']));
        $config->setResultCacheImpl($this->getCache($emOptions['cache']['result']));

        $em = EntityManager::create($this->getConnection($connection), $config);
        
        if (isset($emOptions['logger'])) {
            $dbalConfig = $em->getConnection()->getConfiguration();
            
            $logger = new $emOptions['logger']();
            $dbalConfig->setSqlLogger($logger);
        }

        $this->_entityManagers[$name] = $em;
    }
}
