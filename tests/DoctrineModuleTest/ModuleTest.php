<?php

namespace DoctrineModuleTest;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use DoctrineModule\Module;
use DoctrineModuleTest\ServiceManagerFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @covers \DoctrineModule\Module
 */
class ModuleTest extends TestCase
{

    /**
     * @var MockObject|\Zend\Mvc\Application
     */
    private $application;

    /**
     * @var MockObject|\Zend\Mvc\MvcEvent
     */
    private $event;


    /**
     * @var MockObject|\Zend\ServiceManager\ServiceManager
     */
    private $serviceManager;

    /**
     * @var MockObject|\Symfony\Component\Console\Application
     */
    private $cli;

    public function setUp()
    {
        $this->application    = $this->getMock('Zend\Mvc\Application', [], [], '', false);
        $this->event          = $this->getMock('Zend\Mvc\MvcEvent');
        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');
        $this->cli            = $this->getMock('Symfony\Component\Console\Application', ['run']);

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
