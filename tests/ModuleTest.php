<?php

declare(strict_types=1);

namespace DoctrineModuleTest;

use DoctrineModule\Module;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function serialize;
use function unserialize;

/**
 * @covers \DoctrineModule\Module
 */
class ModuleTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Application */
    private $application;

    /** @var PHPUnit_Framework_MockObject_MockObject|MvcEvent */
    private $event;


    /** @var PHPUnit_Framework_MockObject_MockObject|ServiceManager */
    private $serviceManager;

    /** @var PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Application */
    private $cli;

    protected function setUp(): void
    {
        $this->application    = $this->getMockBuilder('Laminas\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $this->event          = $this->createMock('Laminas\Mvc\MvcEvent');
        $this->serviceManager = $this->createMock('Laminas\ServiceManager\ServiceManager');
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
    public function testGetConfig(): void
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
     *
     * @covers \DoctrineModule\Module::getConsoleUsage
     */
    public function testGetConsoleUsage(): void
    {
        $this
            ->cli
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->isInstanceOf('Symfony\Component\Console\Input\InputInterface'),
                $this->isInstanceOf('Symfony\Component\Console\Output\OutputInterface')
            )
            ->will($this->returnCallback(static function (InputInterface $input, OutputInterface $output): void {
                $output->write($input->getFirstArgument() . ' - TEST');
                $output->write(' - More output');
            }));

        $module = new Module();

        $module->onBootstrap($this->event);

        $this->assertSame(
            'list - TEST - More output',
            $module->getConsoleUsage($this->createMock('Laminas\Console\Adapter\AdapterInterface'))
        );
    }
}
