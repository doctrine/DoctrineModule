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

use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use Symfony\Component\Console\Application;
use Zend\Console\Request;
use Zend\Mvc\Console\Router\RouteMatch;

/**
 * Tests for {@see \DoctrineModule\Mvc\Router\Console\SymfonyCli}
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 *
 * @covers \DoctrineModule\Mvc\Router\Console\SymfonyCli
 */
class SymfonyCliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SymfonyCli
     */
    protected $route;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->route = new SymfonyCli(new Application());
    }

    public function testMatching()
    {
        $this->assertInstanceOf(
            RouteMatch::class,
            $this->route->match(new Request(['scriptname.php', 'list']))
        );
    }

    public function testMatchingWithParams()
    {
        $this->assertInstanceOf(
            RouteMatch::class,
            $this->route->match(new Request(['scriptname.php', 'list', '--help']))
        );
    }

    public function testNotMatching()
    {
        $this->assertNull($this->route->match(new Request(['scriptname.php', 'unknowncommand'])));
    }
}
