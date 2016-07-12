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

use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit_Framework_TestCase;

/**
 * Tests for the Collection pagination adapter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class CollectionAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionAdapter
     */
    protected $adapter;

    /**
     * {@inheritDoc}.
     */
    protected function setUp()
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
        $this->assertEquals(array(), $actual);
    }
}
