<?php
namespace SpiffyDoctrine\Service;
use Doctrine\Common,
    Doctrine\DBAL,
    Doctrine\ORM,
    PDO,
	SpiffyDoctrine\Option\Configuration,
	SpiffyDoctrine\Option\Connection,
	SpiffyDoctrine\Option\EventManager;

class Doctrine
{
    /**
     * Definition for custom functions.
     * 
     * @var array
     */
    protected $customFunctionDefinition = array(
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
    protected $customHydratorDefinition = array(
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
    protected $namedQueryDefinition = array(
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
    protected $namedNativeQueryDefinition = array(
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
    protected $driverChainDefinition = array(
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
    protected $annotationDriverDefinition = array(
        'required' => array(
            'cache_class' => 'string'
        )
    );
    
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * @var Doctrine\ORM\Mapping\Driver\DriverChain
     */
    protected $driverChain;
    
    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $conn;
    
    /**
     * @var Doctrine\ORM\Configuration
     */
    protected $config;
    
    /**
     * @var Doctrine\Common\EventManager
     */
    protected $evm;

    /**    
     * Constructor.
     * 
     * @param array $conn   Connection options.
     * @param array $config Configuration options, @see $configurationDefinition
     * @param array $evm    EventManager options, @see $eventManagerDefinition
     * @param PDO   $pdo    PDO instance, if needed. This is for Zend\Di support. You can also
     *                      pass an instance of PDO to $conn['pdo'].
     * @return void 
     */
    public function __construct(Connection $conn)
    {
    	$this->conn = $conn->getInstance();
    }
    
    /**
     * Get entity manager.
     * 
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
    
    /**
     * Get event manager.
     * 
     * @return Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->evm;
    }
    
    /**
     * Get connection.
     * 
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->conn;
    }
    
    /**
     * Get configuration.
     * 
     * @return Doctrine\ORM\Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }
    
    /**
     * Get driver chain.
     * 
     * @return Doctrine\ORM\Mapping\Driver\DriverChain
     */
    public function getDriverChain()
    {
        return $this->driverChain;
    }
    
    /**
     * Creates the EntityManager from a pre-configured connection, configuration,
     * and event manager present in $conn, $config, and $evm respectively.
     * 
     * @return void
     */
    protected function createEntityManager()
    {
        $this->em = ORM\EntityManager::create($this->conn, $this->config, $this->evm);
    }
    
    /**
     * Creates a connection using the DBAL\DriverManager and a pre-configured
     * Configuration and EventManager. This method assumes the configuration
     * and event manager have been setup and are present in $config and $evm.
     * 
     * @param array $opts
     * @return void
     */
    protected function createConnection(array $opts)
    {
        $this->conn = DBAL\DriverManager::getConnection(
            $opts,
            $this->config,
            $this->evm
        );
    }
}
