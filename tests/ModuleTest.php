<?php

declare(strict_types=1);

namespace DoctrineModuleTest;

use DoctrineModule\Module;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application as SymfonyApplication;

use function serialize;
use function unserialize;

/**
 * @covers \DoctrineModule\Module
 */
class ModuleTest extends TestCase
{
    /** @var MockObject&Application */
    private MockObject $application;

    /** @var MockObject&MvcEvent */
    private MockObject $event;

    /** @var MockObject&ServiceManager */
    private MockObject $serviceManager;

    /** @var MockObject&SymfonyApplication */
    private MockObject $cli;

    protected function setUp(): void
    {
        $this->application    = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->event          = $this->createMock(MvcEvent::class);
        $this->serviceManager = $this->createMock(ServiceManager::class);
        $this->cli            = $this->createPartialMock(SymfonyApplication::class, ['run']);

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

        $this->assertSame($config, unserialize(serialize($config)));
    }
}
