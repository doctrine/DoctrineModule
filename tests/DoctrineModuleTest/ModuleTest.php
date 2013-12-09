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
use PHPUnit_Framework_Assert;
use PHPUnit_Framework_MockObject_MockObject;
use DoctrineModule\Module;
use DoctrineModuleTest\ServiceManagerTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @covers \DoctrineModule\Module
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Zend\Mvc\Application
     */
    private $application;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Zend\Mvc\MvcEvent
     */
    private $event;


    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceManager
     */
    private $serviceManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Application
     */
    private $cli;

    public function setUp()
    {
        $this->application    = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $this->event          = $this->getMock('Zend\Mvc\MvcEvent');
        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');
        $this->cli            = $this->getMock('Symfony\Component\Console\Application', array('run'));

        $this
            ->serviceManager
            ->expects($this->any())
            ->method('get')
            ->with('doctrine.cli')
            ->will($this->returnValue($this->cli));

        $this
            ->application
            ->expects($this->any())
            ->method('getServiceManager')
            ->will($this->returnValue($this->serviceManager));

        $this
            ->event
            ->expects($this->any())
            ->method('getTarget')
            ->will($this->returnValue($this->application));
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
        $this
            ->cli
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->isInstanceOf('Symfony\Component\Console\Input\InputInterface'),
                $this->isInstanceOf('Symfony\Component\Console\Output\OutputInterface')
            )
            ->will($this->returnCallback(function (InputInterface $input, OutputInterface $output) {
                $output->write($input->getFirstArgument() . ' - TEST');
                $output->write(' - More output');
            }));


        $module = new Module();

        $module->onBootstrap($this->event);

        $this->assertSame(
            'list - TEST - More output',
            $module->getConsoleUsage($this->getMock('Zend\Console\Adapter\AdapterInterface'))
        );
    }
}
