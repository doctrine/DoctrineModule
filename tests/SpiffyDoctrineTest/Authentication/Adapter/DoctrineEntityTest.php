<?php
namespace SpiffyDoctrineTest\Authentication\Adapter;
use SpiffyDoctrineTest\Framework\TestCase;

class DoctrineEntityTest extends TestCase 
{
    public function setUp()
    {
        $this->createDb();
    }
    
    public function testInvalidLogin()
    {
        $em = $this->getEntityManager();
        
        $adapter = new \SpiffyDoctrine\Authentication\Adapter\DoctrineEntity(
            $em,
            'SpiffyDoctrineTest\Assets\Entity\Test'
        );
        $adapter->setIdentity('username');
        $adapter->setCredential('password');
        
        $result = $adapter->authenticate();
        
        $this->assertFalse($result->isValid());
    }
    
    public function testValidLogin()
    {
        $em = $this->getEntityManager();
        
        $entity = new \SpiffyDoctrineTest\Assets\Entity\Test;
        $entity->username = 'username';
        $entity->password = 'password';
        $em->persist($entity);
        $em->flush();
        
        $adapter = new \SpiffyDoctrine\Authentication\Adapter\DoctrineEntity(
            $em,
            'SpiffyDoctrineTest\Assets\Entity\Test'
        );
        $adapter->setIdentity('username');
        $adapter->setCredential('password');
        
        $result = $adapter->authenticate();
        
        $this->assertTrue($result->isValid());
    }
}
