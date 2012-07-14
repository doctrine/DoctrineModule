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

namespace DoctrineModuleTest\Cache;

use DoctrineModule\Cache\DoctrineCacheStorage;
use Doctrine\Common\Cache\ArrayCache;
use Zend\Cache\Storage\Adapter\AdapterOptions;
use Zend\EventManager\EventInterface;
use PHPUnit_Framework_TestCase;

/**
 * Tests for the cache bridge
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class DoctrineCacheStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineCacheStorage
     */
    protected $_storage;

    /**
     * @var AdapterOptions
     */
    protected $_options;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->_options = new AdapterOptions();
        $this->_storage = new DoctrineCacheStorage($this->_options, new ArrayCache());
    }

    public function testGetOptions()
    {
        $this->_storage = $this->getMockForAbstractAdapter();

        $options = $this->_storage->getOptions();
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\AdapterOptions', $options);
        $this->assertInternalType('boolean', $options->getWritable());
        $this->assertInternalType('boolean', $options->getReadable());
        $this->assertInternalType('integer', $options->getTtl());
        $this->assertInternalType('string', $options->getNamespace());
        $this->assertInternalType('string', $options->getKeyPattern());
    }

    public function testSetWritable()
    {
        $this->_options->setWritable(true);
        $this->assertTrue($this->_options->getWritable());

        $this->_options->setWritable(false);
        $this->assertFalse($this->_options->getWritable());
    }

    public function testSetReadable()
    {
        $this->_options->setReadable(true);
        $this->assertTrue($this->_options->getReadable());

        $this->_options->setReadable(false);
        $this->assertFalse($this->_options->getReadable());
    }

    public function testSetTtl()
    {
        $this->_options->setTtl('123');
        $this->assertSame(123, $this->_options->getTtl());
    }

    public function testSetTtlThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Zend\Cache\Exception\InvalidArgumentException');
        $this->_options->setTtl(-1);
    }

    public function testGetDefaultNamespaceNotEmpty()
    {
        $ns = $this->_options->getNamespace();
        $this->assertNotEmpty($ns);
    }

    public function testSetNamespace()
    {
        $this->_options->setNamespace('new_namespace');
        $this->assertSame('new_namespace', $this->_options->getNamespace());
    }

    public function testSetNamespace0()
    {
        $this->_options->setNamespace('0');
        $this->assertSame('0', $this->_options->getNamespace());
    }

    public function testSetKeyPattern()
    {
        $this->_options->setKeyPattern('/^[key]+$/Di');
        $this->assertEquals('/^[key]+$/Di', $this->_options->getKeyPattern());
    }

    public function testUnsetKeyPattern()
    {
        $this->_options->setKeyPattern(null);
        $this->assertSame('', $this->_options->getKeyPattern());
    }

    public function testSetKeyPatternThrowsExceptionOnInvalidPattern()
    {
        $this->setExpectedException('Zend\Cache\Exception\InvalidArgumentException');
        $this->_options->setKeyPattern('#');
    }

    public function testGetItemCallsInternalGetItem()
    {
        $storage = $this->getMockForAbstractAdapter(array('internalGetItem'));

        $key    = 'key1';
        $result = 'value1';

        $storage
            ->expects($this->once())
            ->method('internalGetItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue($result));

        $rs = $storage->getItem($key);
        $this->assertEquals($result, $rs);
    }

    public function testGetItemsCallsInternalGetItems()
    {
        $storage = $this->getMockForAbstractAdapter(array('internalGetItems'));

        $keys   = array('key1', 'key2');
        $result = array('key2' => 'value2');

        $storage
            ->expects($this->once())
            ->method('internalGetItems')
            ->with($this->equalTo($keys))
            ->will($this->returnValue($result));

        $rs = $storage->getItems($keys);
        $this->assertEquals($result, $rs);
    }

    public function testHasItemCallsInternalHasItem()
    {
        $storage = $this->getMockForAbstractAdapter(array('internalHasItem'));

        $key    = 'key1';
        $result = true;

        $storage
            ->expects($this->once())
            ->method('internalHasItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue($result));

        $rs = $storage->hasItem($key);
        $this->assertSame($result, $rs);
    }

    public function testHasItemsCallsInternalHasItems()
    {
        $storage = $this->getMockForAbstractAdapter(array('internalHasItems'));

        $keys   = array('key1', 'key2');
        $result = array('key2');

        $storage
            ->expects($this->once())
            ->method('internalHasItems')
            ->with($this->equalTo($keys))
            ->will($this->returnValue($result));

        $rs = $storage->hasItems($keys);
        $this->assertEquals($result, $rs);
    }

    public function testInternalHasItemsCallsInternalHasItem()
    {
        $storage = $this->getMockForAbstractAdapter(array('internalHasItem'));

        $items  = array('key1' => true, 'key2' => false);
        $result = array('key1');

        $i = 0; // method call counter
        foreach ($items as $k => $v) {
            $storage
                ->expects($this->at($i++))
                ->method('internalHasItem')
                ->with($this->equalTo($k))
                ->will($this->returnValue($v));
        }

        $rs = $storage->hasItems(array_keys($items));
        $this->assertEquals($result, $rs);
    }

    public function testGetMetadataCallsInternalGetMetadata()
    {
        $storage = $this->getMockForAbstractAdapter(array('internalGetMetadata'));

        $key    = 'key1';
        $result = array();

        $storage
            ->expects($this->once())
            ->method('internalGetMetadata')
            ->with($this->equalTo($key))
            ->will($this->returnValue($result));

        $rs = $storage->getMetadata($key);
        $this->assertSame($result, $rs);
    }

    public function testPreEventsCanChangeArguments()
    {
        // getItem(s)
        $this->checkPreEventCanChangeArguments('getItem', array(
            'key' => 'key'
        ), array(
            'key' => 'changedKey',
        ));

        $this->checkPreEventCanChangeArguments('getItems', array(
            'keys' => array('key')
        ), array(
            'keys' => array('changedKey'),
        ));

        // hasItem(s)
        $this->checkPreEventCanChangeArguments('hasItem', array(
            'key' => 'key'
        ), array(
            'key' => 'changedKey',
        ));

        $this->checkPreEventCanChangeArguments('hasItems', array(
            'keys' => array('key'),
        ), array(
            'keys' => array('changedKey'),
        ));

        // getMetadata(s)
        $this->checkPreEventCanChangeArguments('getMetadata', array(
            'key' => 'key'
        ), array(
            'key' => 'changedKey',
        ));

        $this->checkPreEventCanChangeArguments('getMetadatas', array(
            'keys' => array('key'),
        ), array(
            'keys' => array('changedKey'),
        ));

        // setItem(s)
        $this->checkPreEventCanChangeArguments('setItem', array(
            'key'   => 'key',
            'value' => 'value',
        ), array(
            'key'   => 'changedKey',
            'value' => 'changedValue',
        ));

        $this->checkPreEventCanChangeArguments('setItems', array(
            'keyValuePairs' => array('key' => 'value'),
        ), array(
            'keyValuePairs' => array('changedKey' => 'changedValue'),
        ));

        // addItem(s)
        $this->checkPreEventCanChangeArguments('addItem', array(
            'key'   => 'key',
            'value' => 'value',
        ), array(
            'key'   => 'changedKey',
            'value' => 'changedValue',
        ));

        $this->checkPreEventCanChangeArguments('addItems', array(
            'keyValuePairs' => array('key' => 'value'),
        ), array(
            'keyValuePairs' => array('changedKey' => 'changedValue'),
        ));

        // replaceItem(s)
        $this->checkPreEventCanChangeArguments('replaceItem', array(
            'key'   => 'key',
            'value' => 'value',
        ), array(
            'key'   => 'changedKey',
            'value' => 'changedValue',
        ));

        $this->checkPreEventCanChangeArguments('replaceItems', array(
            'keyValuePairs' => array('key' => 'value'),
        ), array(
            'keyValuePairs' => array('changedKey' => 'changedValue'),
        ));

        // CAS
        $this->checkPreEventCanChangeArguments('checkAndSetItem', array(
            'token' => 'token',
            'key'   => 'key',
            'value' => 'value',
        ), array(
            'token' => 'changedToken',
            'key'   => 'changedKey',
            'value' => 'changedValue',
        ));

        // touchItem(s)
        $this->checkPreEventCanChangeArguments('touchItem', array(
            'key' => 'key',
        ), array(
            'key' => 'changedKey',
        ));

        $this->checkPreEventCanChangeArguments('touchItems', array(
            'keys' => array('key'),
        ), array(
            'keys' => array('changedKey'),
        ));

        // removeItem(s)
        $this->checkPreEventCanChangeArguments('removeItem', array(
            'key' => 'key',
        ), array(
            'key' => 'changedKey',
        ));

        $this->checkPreEventCanChangeArguments('removeItems', array(
            'keys' => array('key'),
        ), array(
            'keys' => array('changedKey'),
        ));

        // incrementItem(s)
        $this->checkPreEventCanChangeArguments('incrementItem', array(
            'key'   => 'key',
            'value' => 1
        ), array(
            'key'   => 'changedKey',
            'value' => 2,
        ));

        $this->checkPreEventCanChangeArguments('incrementItems', array(
            'keyValuePairs' => array('key' => 1),
        ), array(
            'keyValuePairs' => array('changedKey' => 2),
        ));

        // decrementItem(s)
        $this->checkPreEventCanChangeArguments('decrementItem', array(
            'key'   => 'key',
            'value' => 1
        ), array(
            'key'   => 'changedKey',
            'value' => 2,
        ));

        $this->checkPreEventCanChangeArguments('decrementItems', array(
            'keyValuePairs' => array('key' => 1),
        ), array(
            'keyValuePairs' => array('changedKey' => 2),
        ));
    }

    /**
     * @param string $method
     * @param array  $args
     * @param array  $expectedArgs
     */
    protected function checkPreEventCanChangeArguments($method, array $args, array $expectedArgs)
    {
        $internalMethod = 'internal' . ucfirst($method);
        $eventName      = $method . '.pre';

        // init mock
        $storage = $this->getMockForAbstractAdapter(array($internalMethod));
        $storage->getEventManager()->attach($eventName, function (EventInterface $event) use ($expectedArgs) {
            $params = $event->getParams();
            foreach ($expectedArgs as $k => $v) {
                $params[$k] = $v;
            }
        });

        // set expected arguments of internal method call
        $tmp = $storage->expects($this->once())->method($internalMethod);
        $equals = array();
        foreach ($expectedArgs as $v) {
            $equals[] = $this->equalTo($v);
        }
        call_user_func_array(array($tmp, 'with'), $equals);

        // run
        call_user_func_array(array($storage, $method), $args);
    }

    /**
     * Generates a mock of the abstract storage adapter by mocking all abstract and the given methods
     * Also sets the adapter options
     *
     * @param  array                                                                                $methods
     * @return \Zend\Cache\Storage\Adapter\AbstractAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockForAbstractAdapter(array $methods = array())
    {
        $class = 'Zend\Cache\Storage\Adapter\AbstractAdapter';

        if (!$methods) {
            $adapter = $this->getMockForAbstractClass($class);
        } else {
            $reflection = new \ReflectionClass('Zend\Cache\Storage\Adapter\AbstractAdapter');
            /* @var $method \ReflectionMethod */
            foreach ($reflection->getMethods() as $method) {
                if ($method->isAbstract()) {
                    $methods[] = $method->getName();
                }
            }
            $adapter = $this->getMockBuilder($class)->setMethods(array_unique($methods))->getMock();
        }

        /* @var $adapter \Zend\Cache\Storage\Adapter\AbstractAdapter */
        $adapter->setOptions($this->_options);

        return $adapter;
    }
}
