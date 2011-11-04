<?php
namespace SpiffyDoctrineTest\Authentication\Adapter;
use Doctrine\ORM\EntityManager,
    SpiffyDoctrineTest\Assets,
    SpiffyDoctrineTest\Framework\TestCase;

class DoctrineTest extends TestCase
{
    public function setUp()
    {
        $this->createDb();
    }
    
    public function testDoctrineServiceInstantiates()
    {
        $this->getService();
    }
    
    public function testEntityManagerIsValid()
    {
        $em = $this->getEntityManager();
        $this->assertTrue(($em instanceof EntityManager));
    }
}