<?php

namespace DoctrineModuleTest\Paginator\Adapter;

use Doctrine\Common\Collections\Criteria;
use DoctrineModule\Paginator\Adapter\Selectable as SelectableAdapter;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit_Framework_TestCase;

/**
 * Tests for the Selectable pagination adapter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class SelectableAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \DoctrineModule\Paginator\Adapter\Selectable::getItems
     */
    public function testGetItemsAtOffsetZeroWithEmptyCriteria()
    {
        $selectable = $this->getMock('Doctrine\Common\Collections\Selectable');
        $adapter    = new SelectableAdapter($selectable);

        $me = $this;

        $selectable
            ->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    function (Criteria $criteria) use ($me) {
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
    public function testGetItemsAtOffsetZeroWithNonEmptyCriteria()
    {
        $selectable = $this->getMock('Doctrine\Common\Collections\Selectable');
        $criteria   = new Criteria(Criteria::expr()->eq('foo', 'bar'));
        $adapter    = new SelectableAdapter($selectable, $criteria);

        $me = $this;

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    function (Criteria $innerCriteria) use ($criteria, $me) {
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
    public function testGetItemsAtOffsetTenWithEmptyCriteria()
    {
        $selectable = $this->getMock('Doctrine\Common\Collections\Selectable');
        $adapter    = new SelectableAdapter($selectable);

        $me = $this;

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    function (Criteria $criteria) use ($me) {
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
    public function testGetItemsAtOffsetTenWithNonEmptyCriteria()
    {
        $selectable = $this->getMock('Doctrine\Common\Collections\Selectable');
        $criteria   = new Criteria(Criteria::expr()->eq('foo', 'bar'));
        $adapter    = new SelectableAdapter($selectable, $criteria);

        $me = $this;

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    function (Criteria $innerCriteria) use ($criteria, $me) {
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
    public function testReturnsCorrectCount()
    {
        $selectable = $this->getMock('Doctrine\Common\Collections\Selectable');
        $expression = Criteria::expr()->eq('foo', 'bar');
        $criteria   = new Criteria($expression, ['baz' => Criteria::DESC], 10, 20);
        $adapter    = new SelectableAdapter($selectable, $criteria);

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    function (Criteria $criteria) use ($expression) {
                        return $criteria->getWhereExpression() == $expression
                            && (['baz' => Criteria::DESC] === $criteria->getOrderings())
                            && null === $criteria->getFirstResult()
                            && null === $criteria->getMaxResults();
                    }
                )
            )
            ->will($this->returnValue(new ArrayCollection(range(1, 101))));

        $this->assertEquals(101, $adapter->count());

        $this->assertSame(10, $criteria->getFirstResult(), 'Original criteria was not modified');
        $this->assertSame(20, $criteria->getMaxResults(), 'Original criteria was not modified');
    }
}
