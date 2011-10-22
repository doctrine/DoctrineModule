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
     * Configuration.
     * @var array
     */
    protected $_config = array();
    
    /**
     * Constructor.
     * 
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->_config = $config;
    }

    /**
     * Get a cache instance.
     * 
     * @param string $name
     * @return Doctrine\Common\Cache\AbstractCache
     */
    public function getCache($name = null)
    {
        $name = $name ? $name : self::DEFAULT_KEY;

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
    public function getConnection($name = null)
    {
        $name = $name ? $name : self::DEFAULT_KEY;

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
    public function getEventManager($name = null)
    {
        $name = $name ? $name : self::DEFAULT_KEY;
    
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
    public function getEntityManager($name = null)
    {
        $name = $name ? $name : self::DEFAULT_KEY;

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
        if (!isset($this->_config['cache'][$name])) {
            throw new InvalidArgumentException(
                "Cache with index '{$name}' could not be located.");
        }

        $cacheOptions = $this->_config['cache'][$name];
        $cache = new $cacheOptions['class'];

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
        if (!isset($this->_config['dbal']['connection'][$name])) {
            throw new InvalidArgumentException(
                "Connection with index '{$name}' could not be located.");
        }

        $conOptions = $this->_config['dbal']['connection'][$name];
        $this->_connections[$name] = DriverManager::getConnection(
            $conOptions,
            null,
            $this->getEventManager(
                $this->_config['dbal']['connection'][$name]['evm']
            )
        );
    }
    
    /**
     * Prepares an eveent manager instance.
     * 
     * @param string $name
     */
    protected function _prepareEventManager($name)
    {
        if (!isset($this->_config['evm'][$name])) {
            throw new InvalidArgumentException(
                "EventManager with index '{$name}' could not be located.");
        }
        
        $evmOptions = $this->_config['evm'][$name];
        
        $evm = new EventManager();
        if (isset($evmOptions['subscribers']) && is_array($evmOptions['subscribers'])) {
            foreach($evmOptions['subscribers'] as $subscriber) {
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
        if (!isset($this->_config['orm']['em'][$name])) {
            throw new \InvalidArgumentException(
                "EntityManager with name '{$name}' could not be located.");
        }

        $emOptions = $this->_config['orm']['em'][$name];
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
        
        if (isset($emOptions['logger']['class'])) {
            $dbalConfig = $em->getConnection()->getConfiguration();
            
            $logger = new $emOptions['logger']['class']();
            $dbalConfig->setSqlLogger($logger);
        }

        $this->_entityManagers[$name] = $em;
    }
}
