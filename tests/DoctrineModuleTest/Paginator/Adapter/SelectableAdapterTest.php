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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

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
        $criteria   = new Criteria($expression, array('baz' => Criteria::DESC), 10, 20);
        $adapter    = new SelectableAdapter($selectable, $criteria);

        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    function (Criteria $criteria) use ($expression) {
                        return $criteria->getWhereExpression() == $expression
                            && (array('baz' => Criteria::DESC) === $criteria->getOrderings())
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
