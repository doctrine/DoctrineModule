<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Paginator\Adapter;

use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use PHPUnit\Framework\TestCase;

use function range;

/**
 * Tests for the Collection pagination adapter
 */
class CollectionAdapterTest extends TestCase
{
    protected CollectionAdapter $adapter;

    /**
     * {@inheritDoc}.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new CollectionAdapter(new ArrayCollection(range(1, 101)));
    }

    public function testGetsItemsAtOffsetZero(): void
    {
        $expected = range(1, 10);
        $actual   = $this->adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testGetsItemsAtOffsetTen(): void
    {
        $expected = range(11, 20);
        $actual   = $this->adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsCorrectCount(): void
    {
        $this->assertEquals(101, $this->adapter->count());
    }

    public function testEmptySet(): void
    {
        $adapter = new CollectionAdapter(new ArrayCollection());
        $actual  = $adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }
}
