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
 * and is licensed under the MIT license.
 */

namespace DoctrineModuleTest\Controller;

use Zend\Console\Request;
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
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
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../TestConfiguration.php.dist');
        parent::setUp();

        $this->getApplicationServiceLocator()->get('doctrine.cli')
            ->add(new FailingCommand());
    }

    /**
     * Verifies that the controller handling the DoctrineModule CLI functionality can be reached
     */
    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch(new Request(array('scriptname.php', 'list')));

        $this->assertResponseStatusCode(0);
        $this->assertModuleName('doctrinemodule');
        $this->assertControllerName('doctrinemodule\controller\cli');
        $this->assertControllerClass('clicontroller');
        $this->assertActionName('cli');
        $this->assertMatchedRouteName('doctrine_cli');
    }

    public function testNonZeroExitCode()
    {
        $this->dispatch(new Request(array('scriptname.php', 'fail')));

        $this->assertNotResponseStatusCode(0);
    }

    public function testException()
    {
        $this->dispatch(new Request(array('scriptname.php', '-q', 'fail', '--exception')));

        $this->assertNotResponseStatusCode(0);
    }
}
