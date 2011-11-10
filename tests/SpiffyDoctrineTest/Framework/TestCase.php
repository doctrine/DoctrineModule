<?php
namespace SpiffyDoctrineTest\Framework;
use PHPUnit_Framework_TestCase,
    SpiffyDoctrine\Service\Doctrine;

class TestCase extends PHPUnit_Framework_TestCase
{
    public static $locator;
    
    /**
     * @var boolean
     */
    protected static $hasDb = false;
    
    /**
     * @var SpiffyDoctrine\Service\Doctrine
     */
    protected $_service;
    
    /**
     * Creates a database if not done already.
     */
    public function createDb()
    {
        if (self::$hasDb) {
            return;
        }
        
        $em = $this->getEntityManager();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $classes = array($em->getClassMetadata('SpiffyDoctrineTest\Assets\Entity\Test'));
        $tool->createSchema($classes);
        self::$hasDb = true;
    }
    
    public function getLocator()
    {
    	return self::$locator;
    }
    
    /**
     * Get EntityManager.
     * 
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getLocator()->get('doctrine_em');
    }
}
