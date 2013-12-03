<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
 
namespace DoctrineModuleTest;

use PHPUnit_Framework_TestCase;
use DoctrineModule\Module;
use DoctrineModuleTest\ServiceManagerTestCase;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @covers \DoctrineModule\Module
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
        
        $this->assertSame($config, unserialize(serialize($config)));
    }
    
    /**
     * Should display the help message in plain message
     */
    public function testGetConsoleUsage()
    {
        $console = $this->getMock('Zend\Console\Adapter\AbstractAdapter');
        
        $eventMock = $this->eventMock;
        $sm = $eventMock->getTarget()->getServiceManager();
        $cli = $sm->get('doctrine.cli');
        
        $excepted = $cli->getHelp();
        $excepted = strip_tags($excepted);
        
        $module = new Module();
        $module->onBootstrap($eventMock);
        
        $actual = $module->getConsoleUsage($console);
        
        $this->assertStringStartsWith('DoctrineModule Command Line Interface', $actual);
        $this->assertStringEndsWith('Lists commands' . "\n", $actual);
    }
}
