<?php
namespace SpiffyDoctrineTest\Framework;
use PHPUnit_Framework_TestCase,
    SpiffyDoctrine\Service\Doctrine;

class TestCase extends PHPUnit_Framework_TestCase
{
    public static $config;
    
    /**
     * @var boolean
     */
    protected $_hasDb = false;
    
    /**
     * @var SpiffyDoctrine\Service\Doctrine
     */
    protected $_service;
    
    /**
     * Creates a database if not done already.
     */
    public function createDb()
    {
        if ($this->_hasDb) {
            return;
        }
        
        $em = $this->getEntityManager();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $classes = array(
            $em->getClassMetadata('SpiffyDoctrineTest\Assets\Entity\Test'),
        );
        $tool->createSchema($classes);
    }
    
    /**
     * Get EntityManager.
     * 
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getService()->getEntityManager();
    }
    
    /**
     * Get Doctrine service.
     * 
     * @return SpiffyDoctrine\Service\Doctrine
     */
    public function getService()
    {
        if (null === $this->_service) {
            $config = self::$config;
            $this->_service = new Doctrine(
                $config['conn'],
                $config['config'],
                $config['evm']
            );
        }
        return $this->_service;
    }
}
