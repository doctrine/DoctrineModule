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

namespace DoctrineModuleTest\Mvc\Router\Console;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request;
use Zend\Mvc\Router\RoutePluginManager;
use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use DoctrineModuleTest\ServiceManagerTestCase;

class SymfonyCliTest extends ServiceManagerTestCase
{

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $routePluginManager;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->routePluginManager = new RoutePluginManager();

        $this->routePluginManager->setServiceLocator($this->getServiceManager());
        parent::setUp();
    }

    public function testMatching()
    {
        $request = new Request(array('scriptname.php', 'list'));
        $route = new SymfonyCli();
        $route->setServiceLocator($this->routePluginManager);
        $match = $route->match($request);

        $this->assertInstanceOf('Zend\Mvc\Router\Console\RouteMatch', $match, "The route matches");
    }

    public function testMatchingWithParams()
    {
        $request = new Request(array('scriptname.php', 'list', '--help'));
        $route = new SymfonyCli();
        $route->setServiceLocator($this->routePluginManager);
        $match = $route->match($request);

        $this->assertInstanceOf('Zend\Mvc\Router\Console\RouteMatch', $match, "The route matches");
    }

    public function testNotMatching()
    {
        $request = new Request(array('scriptname.php', 'unknowncommand'));
        $route = new SymfonyCli();
        $route->setServiceLocator($this->routePluginManager);
        $match = $route->match($request);

        $this->assertNull($match, "The route must not match");
    }
}
