<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Mvc\Router\Console;

use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use Laminas\Console\Request;
use Laminas\Router\RouteMatch;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

/**
 * Tests for {@see \DoctrineModule\Mvc\Router\Console\SymfonyCli}
 *
 * @covers \DoctrineModule\Mvc\Router\Console\SymfonyCli
 */
class SymfonyCliTest extends TestCase
{
    /** @var SymfonyCli */
    protected $route;

    protected function setUp(): void
    {
        $this->route = new SymfonyCli(new Application());
    }

    public function testMatching(): void
    {
        $this->assertInstanceOf(
            RouteMatch::class,
            $this->route->match(new Request(['scriptname.php', 'list']))
        );
    }

    public function testMatchingWithParams(): void
    {
        $this->assertInstanceOf(
            RouteMatch::class,
            $this->route->match(new Request(['scriptname.php', 'list', '--help']))
        );
    }

    public function testNotMatching(): void
    {
        $this->assertNull($this->route->match(new Request(['scriptname.php', 'unknowncommand'])));
    }
}
