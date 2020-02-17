<?php

namespace DoctrineModuleTest\Paginator\Adapter;

use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Collection pagination adapter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class CollectionAdapterTest extends TestCase
{
    /**
     * @var CollectionAdapter
     */
    protected $adapter;

    /**
     * {@inheritDoc}.
     */
    protected function setUp() : void
    {
        parent::setUp();
        $this->adapter = new CollectionAdapter(new ArrayCollection(range(1, 101)));
    }

    public function testGetsItemsAtOffsetZero()
    {
        $expected = range(1, 10);
        $actual   = $this->adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testGetsItemsAtOffsetTen()
    {
        $expected = range(11, 20);
        $actual   = $this->adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsCorrectCount()
    {
        $this->assertEquals(101, $this->adapter->count());
    }

    public function testEmptySet()
    {
        $adapter = new CollectionAdapter(new ArrayCollection());
        $actual  = $adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }
}
