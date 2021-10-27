<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Controller;

use DoctrineModuleTest\Controller\Mock\FailingCommand;
use DoctrineModuleTest\ServiceManagerFactory;
use Laminas\Console\Request;
use Laminas\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Tests for {@see \DoctrineModule\Controller\CliController}
 *
 * @covers \DoctrineModule\Controller\CliController
 */
class CliControllerTest extends AbstractConsoleControllerTestCase
{
    /** @var BufferedOutput */
    private $output;

    protected function setUp(): void
    {
        $this->setApplicationConfig(ServiceManagerFactory::getConfiguration());
        parent::setUp();

        $this->output = new BufferedOutput();
        $controller   = $this->getApplicationServiceLocator()
             ->get('ControllerManager')
             ->get('DoctrineModule\Controller\Cli');
        $controller->setOutput($this->output);

        $this->getApplicationServiceLocator()->get('doctrine.cli')
            ->add(new FailingCommand());
    }

    /**
     * Verifies that the controller handling the DoctrineModule CLI functionality can be reached
     */
    public function testIndexActionCanBeAccessed(): void
    {
        $this->dispatch(new Request(['scriptname.php', 'list']));

        $this->assertResponseStatusCode(0);
        $this->assertModuleName('doctrinemodule');
        $this->assertControllerName('doctrinemodule\controller\cli');
        $this->assertControllerClass('clicontroller');
        $this->assertActionName('cli');
        $this->assertMatchedRouteName('doctrine_cli');
    }

    public function testNonZeroExitCode(): void
    {
        $this->dispatch(new Request(['scriptname.php', 'fail']));

        $this->assertNotResponseStatusCode(0);
    }

    public function testException(): void
    {
        $this->dispatch(new Request(['scriptname.php', '-q', 'fail', '--exception']));

        $this->assertNotResponseStatusCode(0);
    }
}
