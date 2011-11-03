<?php
namespace SpiffyDoctrine\Service;
use Doctrine\Common,
    Doctrine\DBAL,
    Doctrine\ORM;

class Doctrine
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;
    
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
    
    public function __construct(array $conn, array $config, array $evm = null)
    {
        $this->_createConfiguration($config);
        $this->_createEventManager($evm);
        $this->_createConnection($conn);
    }
    
    public function getEntityManager()
    {
        
    }
    
    protected function _createConfiguration(array $opts) 
    {
        $defs = array(
            'proxy-dir'       => 'string',
            'proxy-namespace' => 'string'
        );
        
        $this->_validateConfigurationOptions($opts, $defs);
        
        $configuration = new ORM\Configuration;
        $configuration->setAutoGenerateProxyClasses(
            (!isset($opts['auto-generate-proxies']) || (bool) $opts['auto-generate-proxies'])
        );
        
        echo '<pre>';
        print_r($opts);
        exit;
    }
    
    protected function _validateConfigurationOptions(array $opts, array $defs)
    {
        foreach($defs as $var => $type) {
            if (!isset($opts[$var])) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing configuration: "%s" is a required parameter.',
                    $var
                ));
            }
            
            if (null !== $type) {
                $got = gettype($opts[$var]);
                if ($got !== $type) {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid configuration: "%s" should be a %s, got %s.',
                        $var,
                        $type,
                        $got
                    ));
                }
            }
        }
    }
}
