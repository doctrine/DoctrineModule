<?php
namespace DoctrineModuleTest;

use PHPUnit_Framework_TestCase;
use DoctrineModule\Module;
use DoctrineModuleTest\ServiceManagerTestCase;

/**
 * @covers DoctrineModule\Module
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{

    public function testInterfaces()
    {
        $module = new Module();
        
        $this->assertInstanceOf('Zend\ModuleManager\Feature\InitProviderInterface', $module);
        $this->assertInstanceOf('Zend\ModuleManager\Feature\BootstrapListenerInterface', $module);
        $this->assertInstanceOf('Zend\ModuleManager\Feature\ConfigProviderInterface', $module);
        $this->assertInstanceOf('Zend\ModuleManager\Feature\ConfigProviderInterface', $module);
    }

    public function testOnBootstrap()
    {
        $module = new Module();
        
        $serviceManagerUtil = new ServiceManagerTestCase();
        
        $appMock = $this->getMock('Zend\Mvc\Application', array(), array(
            array(),
            $serviceManagerUtil->getServiceManager()
        ));
        $appMock->expects($this->any())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManagerUtil->getServiceManager()));
        
        $event = $this->getMock('Zend\EventManager\Event');
        $event->expects($this->any())
            ->method('getTarget')
            ->will($this->returnValue($appMock));
        
        $module->onBootstrap($event);
    }

    public function testGetConfig()
    {
        $module = new Module();
        
        $config = $module->getConfig();
        
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('doctrine', $config);
        $this->assertArrayHasKey('doctrine_factories', $config);
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertArrayHasKey('controllers', $config);
        $this->assertArrayHasKey('route_manager', $config);
        $this->assertArrayHasKey('console', $config);
    }

    public function testGetConsoleUsage()
    {
        $this->markTestIncomplete(
            'Need further work'
        );
        
        $serviceManagerUtil = new ServiceManagerTestCase();
        
        $appMock = $this->getMock('Zend\Mvc\Application', array(), array(
            array(),
            $serviceManagerUtil->getServiceManager()
        ));
        $appMock->expects($this->any())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManagerUtil->getServiceManager()));
        
        $event = $this->getMock('Zend\EventManager\Event');
        $event->expects($this->any())
            ->method('getTarget')
            ->will($this->returnValue($appMock));
        
        $module = new Module();
        $module->onBootstrap($event);
        
        $console = $this->getMock('Zend\Console\Adapter\AbstractAdapter');
        
        // $this->assertTrue(is_array($module->getConsoleUsage($console)));
        // $this->assertCount(8, $module->getConsoleUsage($console));
    }
}
