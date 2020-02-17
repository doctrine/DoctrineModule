<?php

namespace DoctrineModuleTest\Controller;

use DoctrineModuleTest\ServiceManagerFactory;
use Symfony\Component\Console\Output\BufferedOutput;
use Laminas\Console\Request;
use Laminas\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use DoctrineModuleTest\Controller\Mock\FailingCommand;

/**
 * Tests for {@see \DoctrineModule\Controller\CliController}
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 *
 * @covers \DoctrineModule\Controller\CliController
 */
class CliControllerTest extends AbstractConsoleControllerTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->setApplicationConfig(ServiceManagerFactory::getConfiguration());
        parent::setUp();

        $this->output = new BufferedOutput();
        $controller = $this->getApplicationServiceLocator()->get('ControllerManager')->get('DoctrineModule\Controller\Cli');
        $controller->setOutput($this->output);

        $this->getApplicationServiceLocator()->get('doctrine.cli')
            ->add(new FailingCommand());
    }

    /**
     * Verifies that the controller handling the DoctrineModule CLI functionality can be reached
     */
    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch(new Request(['scriptname.php', 'list']));

        $this->assertResponseStatusCode(0);
        $this->assertModuleName('doctrinemodule');
        $this->assertControllerName('doctrinemodule\controller\cli');
        $this->assertControllerClass('clicontroller');
        $this->assertActionName('cli');
        $this->assertMatchedRouteName('doctrine_cli');
    }

    public function testNonZeroExitCode()
    {
        $this->dispatch(new Request(['scriptname.php', 'fail']));

        $this->assertNotResponseStatusCode(0);
    }

    public function testException()
    {
        $this->dispatch(new Request(['scriptname.php', '-q', 'fail', '--exception']));

        $this->assertNotResponseStatusCode(0);
    }
}
