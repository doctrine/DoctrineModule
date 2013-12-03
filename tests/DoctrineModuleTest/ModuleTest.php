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
use PHPUnit_Framework_MockObject_MockObject;
use DoctrineModule\Module;
use DoctrineModuleTest\ServiceManagerTestCase;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @covers \DoctrineModule\Module
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{

    /**
     * 
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $appMock;

    /**
     * 
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $eventMock;

    public function setUp()
    {
        $serviceManagerUtil = new ServiceManagerTestCase();
        
        $this->appMock = $this->getMock('Zend\Mvc\Application', array(), array(
            array(),
            $serviceManagerUtil->getServiceManager()
        ));
        $this->appMock->expects($this->any())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManagerUtil->getServiceManager()));
        
        $this->eventMock = $this->getMock('Zend\EventManager\Event');
        $this->eventMock->expects($this->any())
            ->method('getTarget')
            ->will($this->returnValue($this->appMock));
    }
    
    /**
     * @covers \DoctrineModule\Module
     */
    public function testInterfaces()
    {
        $module = new Module();
        
        $this->assertInstanceOf('Zend\ModuleManager\Feature\InitProviderInterface', $module);
        $this->assertInstanceOf('Zend\ModuleManager\Feature\BootstrapListenerInterface', $module);
        $this->assertInstanceOf('Zend\ModuleManager\Feature\ConfigProviderInterface', $module);
        $this->assertInstanceOf('Zend\ModuleManager\Feature\ConfigProviderInterface', $module);
    }

    /**
     * @covers \DoctrineModule\Module::onBootstrap
     */
    public function testOnBootstrap()
    {
        $module = new Module();
        $module->onBootstrap($this->eventMock);
    }

    /**
     * @covers \DoctrineModule\Module::getConfig
     */
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
     * @covers \DoctrineModule\Module::getConsoleUsage
     */
    public function testGetConsoleUsage()
    {
        $cliMock = $this->getMock('Symfony\Component\Console\Application', array(
            'setDispatcher',
            'run'
        ), array(), '', false, false);
        $cliMock->expects($this->any())
            ->method('run')
            ->will($this->returnCallback(function ($input, $output)
        {
            if ($input == 'list') {
                $output->write('start', true);
                $output->write('Line2', true);
                $output->write('Line3');
                $output->write('Line4');
                $output->write('end');
            }
        }));
        
        $sm = $this->eventMock->getTarget()->getServiceManager();
        $cliOriginal = $sm->get('doctrine.cli');
        
        $sm->setAllowOverride(true);
        $sm->setService('doctrine.cli', $cliMock);
        
        $module = new Module();
        $module->onBootstrap($this->eventMock);
        
        $console = $this->getMock('Zend\Console\Adapter\AbstractAdapter');
        $actual = $module->getConsoleUsage($console);
        
        $this->assertStringMatchesFormat("start%aend", $actual);
        
        $sm->setService('doctrine.cli', $cliOriginal);
        $sm->setAllowOverride(false);
    }
}
