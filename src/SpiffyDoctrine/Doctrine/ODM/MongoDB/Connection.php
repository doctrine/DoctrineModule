<?php
namespace SpiffyDoctrine\Doctrine\ODM\MongoDB;
use Doctrine\MongoDB\Connection as MongoConnection,
    SpiffyDoctrine\Doctrine\Common\EventManager,
    SpiffyDoctrine\Doctrine\Instance;

class Connection extends Instance
{
	/**
	 * @var Doctrine\ORM\Configuration
	 */
	protected $config;
	
	/**
	 * @var Doctrine\Common\EventManager
	 */
	protected $evm;
    
    /**
     * @var null|Mongo
     */
    protected $server;
    
    /**
     * @var array
     */
    protected $options = array();
	
	/**
	 * Constructor
	 * 
	 * @param null|Mongo 	$server
     * @param array         $options
	 * @param Configuration $config
	 * @param EventManager  $evm
	 */
	public function __construct($server = null, array $options = array(), Configuration $config = null, EventManager $evm = null)
	{
	    $this->server  = $server;
        $this->options = $options ? $options : array();
		$this->config  = $config ? $config->getInstance() : null;
		$this->evm     = $evm ? $evm->getInstance() : null;
		
		parent::__construct(array());
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SpiffyDoctrine\Instance.Instance::loadInstance()
	 */
	protected function loadInstance()
	{
        $this->instance = new MongoConnection(
            $this->server,
            $this->options,
            $this->config,
            $this->evm
        );
	}
}