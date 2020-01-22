<?php

namespace DoctrineModuleTest\Mvc\Router\Console;

use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Laminas\Console\Request;
use Laminas\Router\RouteMatch;

/**
 * Tests for {@see \DoctrineModule\Mvc\Router\Console\SymfonyCli}
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 *
 * @covers \DoctrineModule\Mvc\Router\Console\SymfonyCli
 */
class SymfonyCliTest extends TestCase
{
    /**
     * @var \DoctrineModule\Mvc\Router\Console\SymfonyCli
     */
    protected $route;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
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
