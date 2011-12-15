<?php
namespace SpiffyDoctrine\Doctrine\Common;
use InvalidArgumentException,
    ReflectionClass,
    Doctrine\Common\Cache\Cache,
	Doctrine\Common\Annotations\AnnotationReader,
	Doctrine\Common\Annotations\CachedReader,
	Doctrine\Common\Annotations\IndexedReader,
    SpiffyDoctrine\Doctrine\Instance;

abstract class DriverChain extends Instance
{
    protected $annotationDriverClass = 'Doctrine\ORM\Mapping\Driver\AnnotationDriver';
    protected $driverChainClass      = 'Doctrine\ORM\Mapping\Driver\DriverChain';
    
	/**
	 * @var array
	 */
	protected $driverChainDefinition = array(
        'required' => array(
            'class' 	=> 'string',
            'namespace' => 'string',
            'paths' 	=> 'array',
        ),
        'optional' => array(
            'defaultNamespace' => 'string'
        )
    );
	
	/**
	 * @var Doctrine\Common\Annotations\CachedReader
	 */
	protected static $cachedReader;
	
	/**
	 * @var Doctrine\Common\Cache\Cache
	 */
	protected $cache;
	
	/**
	 * Constructor.
	 * 
	 * @param array $drivers
	 * @param Cache $cache
	 */
	public function __construct(array $drivers = array(), Cache $cache)
	{
		$this->cache = $cache;
		parent::__construct($drivers);
	}
    
	/**
	 * (non-PHPdoc)
	 * @see SpiffyDoctrineMongoODM\Instance.Instance::loadInstance()
	 */
	protected function loadInstance()
	{
		$drivers = $this->getOptions();
		
        $wrapperClass = $this->driverChainClass;
        if (isset($opts['wrapperClass'])) {
            if (is_subclass_of($opts['wrapperClass'], $wrapperClass)) {
               $wrapperClass = $opts['wrapperClass'];
            } else {
                throw InvalidArgumentException(sprintf(
                	'wrapperClass must be an instance of %s, %s given',
                	$this->driverChainClass,
                	$wrapperClass
                ));
            }
        }
		
        $chain = new $wrapperClass;
        
        foreach($drivers as $driverOpts) {
            $this->validateOptions($driverOpts, $this->driverChainDefinition);
            
            if (($driverOpts['class'] == $this->annotationDriverClass) ||
            	(is_subclass_of($driverOpts['class'], $this->annotationDriverClass))
			) {
                $cachedReader = $this->getCachedReader($driverOpts['defaultNamespace']);
                $driver = new $driverOpts['class']($cachedReader, $driverOpts['paths']);
            } else {
                $driver = new $driverOpts['class']($driverOpts['paths']);
            }
            $chain->addDriver($driver, $driverOpts['namespace']);
        }

        $this->instance = $chain; 
    }
    
    /**
     * Get the cached reader instance for annotation readers.
     * 
     * @todo investigate use cases for indexed reader
     * @param  null|string $defaultNamespace
     * @return Doctrine\Common\Annotations\CachedReader
     */
    protected function getCachedReader($defaultNamespace)
    {
    	if (null === self::$cachedReader) {
	    	$reader = new AnnotationReader;
            $reader->setDefaultAnnotationNamespace($defaultNamespace);
            
			//$indexedReader 	    = new IndexedReader($reader);
			//self::$cachedReader = new CachedReader($indexedReader, $this->cache);
			self::$cachedReader = new CachedReader($reader, $this->cache);
    	}
    	return self::$cachedReader;
    }
}