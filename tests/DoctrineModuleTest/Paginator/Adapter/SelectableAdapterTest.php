<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Paginator\Adapter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use DoctrineModule\Paginator\Adapter\Selectable as SelectableAdapter;
use PHPUnit\Framework\TestCase;
use function range;

/**
 * Tests for the Selectable pagination adapter
 *
 * @link    http://www.doctrine-project.org/
 */
class SelectableAdapterTest extends TestCase
{
    /**
     * @covers \DoctrineModule\Paginator\Adapter\Selectable::getItems
     */
    public function testGetItemsAtOffsetZeroWithEmptyCriteria() : void
    {
        $selectable = $this->createMock('Doctrine\Common\Collections\Selectable');
        $adapter    = new SelectableAdapter($selectable);

        $me = $this;

        $selectable
            ->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $criteria) use ($me) {
                        $me->assertEquals(0, $criteria->getFirstResult());
                        $me->assertEquals(10, $criteria->getMaxResults());

                        return true;
                    }
                )
            )
            ->will($this->returnValue(new ArrayCollection(range(1, 10))));

        $expected = range(1, 10);
        $actual   = $adapter->getItems(0, 10);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \DoctrineModule\Paginator\Adapter\Selectable::getItems
     */
    public function testGetItemsAtOffsetZeroWithNonEmptyCriteria() : void
    {
        $selectable = $this->createMock('Doctrine\Common\Collections\Selectable');
        $criteria   = new Criteria(Criteria::expr()->eq('foo', 'bar'));
        $adapter    = new SelectableAdapter($selectable, $criteria);

        $me = $this;

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $innerCriteria) use ($criteria, $me) {
                        // Criteria are cloned internally
                        $me->assertNotEquals($innerCriteria, $criteria);
                        $me->assertEquals(0, $innerCriteria->getFirstResult());
                        $me->assertEquals(10, $innerCriteria->getMaxResults());
                        $me->assertEquals($innerCriteria->getWhereExpression(), $criteria->getWhereExpression());

                        return true;
                    }
                )
            )
            ->will($this->returnValue(new ArrayCollection(range(1, 10))));

        $expected = range(1, 10);
        $actual   = $adapter->getItems(0, 10);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \DoctrineModule\Paginator\Adapter\Selectable::getItems
     */
    public function testGetItemsAtOffsetTenWithEmptyCriteria() : void
    {
        $selectable = $this->createMock('Doctrine\Common\Collections\Selectable');
        $adapter    = new SelectableAdapter($selectable);

        $me = $this;

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $criteria) use ($me) {
                        $me->assertEquals(10, $criteria->getFirstResult());
                        $me->assertEquals(10, $criteria->getMaxResults());

                        return true;
                    }
                )
            )
            ->will($this->returnValue(new ArrayCollection(range(11, 20))));

        $expected = range(11, 20);
        $actual   = $adapter->getItems(10, 10);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \DoctrineModule\Paginator\Adapter\Selectable::getItems
     */
    public function testGetItemsAtOffsetTenWithNonEmptyCriteria() : void
    {
        $selectable = $this->createMock('Doctrine\Common\Collections\Selectable');
        $criteria   = new Criteria(Criteria::expr()->eq('foo', 'bar'));
        $adapter    = new SelectableAdapter($selectable, $criteria);

        $me = $this;

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $innerCriteria) use ($criteria, $me) {
                        // Criteria are cloned internally
                        $me->assertNotEquals($innerCriteria, $criteria);
                        $me->assertEquals(10, $innerCriteria->getFirstResult());
                        $me->assertEquals(10, $innerCriteria->getMaxResults());
                        $me->assertEquals($innerCriteria->getWhereExpression(), $criteria->getWhereExpression());

                        return true;
                    }
                )
            )
            ->will($this->returnValue(new ArrayCollection(range(11, 20))));

        $expected = range(11, 20);
        $actual   = $adapter->getItems(10, 10);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \DoctrineModule\Paginator\Adapter\Selectable::count
     */
    public function testReturnsCorrectCount() : void
    {
        $selectable = $this->createMock('Doctrine\Common\Collections\Selectable');
        $expression = Criteria::expr()->eq('foo', 'bar');
        $criteria   = new Criteria($expression, ['baz' => Criteria::DESC], 10, 20);
        $adapter    = new SelectableAdapter($selectable, $criteria);

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $criteria) use ($expression) {
                        return $criteria->getWhereExpression() === $expression
                            && ($criteria->getOrderings() === ['baz' => Criteria::DESC])
                            && $criteria->getFirstResult() === null
                            && $criteria->getMaxResults() === null;
                    }
                )
            )
            ->will($this->returnValue(new ArrayCollection(range(1, 101))));

        $this->assertEquals(101, $adapter->count());

        $this->assertSame(10, $criteria->getFirstResult(), 'Original criteria was not modified');
        $this->assertSame(20, $criteria->getMaxResults(), 'Original criteria was not modified');
    }
}
