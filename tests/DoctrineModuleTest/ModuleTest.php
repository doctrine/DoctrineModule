<?php

namespace DoctrineModuleTest;

use PHPUnit\Framework\TestCase;
use DoctrineModule\Module;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @covers \DoctrineModule\Module
 */
class ModuleTest extends TestCase
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

    protected function setUp()
    {
        $this->application    = $this->getMockBuilder('Zend\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->event          = $this->createMock('Zend\Mvc\MvcEvent');
        $this->serviceManager = $this->createMock('Zend\ServiceManager\ServiceManager');
        $this->cli            = $this->createPartialMock('Symfony\Component\Console\Application', ['run']);

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

        $this->assertIsArray($config);
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
            $module->getConsoleUsage($this->createMock('Zend\Console\Adapter\AdapterInterface'))
        );
    }
}
