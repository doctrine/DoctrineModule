<?php

namespace DoctrineModuleTest\Form\Element;

use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Form\Element\ObjectSelect;

/**
 * Tests for the ObjectSelect element
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 * @covers  DoctrineModule\Form\Element\ObjectSelect
 */
class ObjectSelectTest extends ProxyAwareElementTestCase
{
    /**
     * @var ArrayCollection
     */
    protected $values;

    /**
     * @var ObjectSelect
     */
    protected $element;

    /**
     * {@inheritDoc}.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->element = new ObjectSelect();

        $this->prepareProxy();
    }

    public function testSetValueWithCollection()
    {
        $this->element->setAttribute('multiple', true);

        $this->element->setValue(
            $this->values
        );

        $this->assertEquals(
            [1, 2],
            $this->element->getValue()
        );
    }

    public function testSetValueWithArray()
    {
        $this->element->setAttribute('multiple', true);

        $this->element->setValue(
            $this->values->toArray()
        );

        $this->assertEquals(
            [1, 2],
            $this->element->getValue()
        );
    }

    public function testSetValueSingleValue()
    {
        $value = $this->values->toArray();

        $this->element->setValue(
            $value[0]
        );

        $this->assertEquals(
            1,
            $this->element->getValue()
        );
    }

    public function testGetValueOptionsDoesntCauseInfiniteLoopIfProxyReturnsEmptyArrayAndValidatorIsInitialized()
    {
        $element = $this->getMock(get_class($this->element), ['setValueOptions']);

        $options = [];

        $proxy = $this->getMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->exactly(2))
              ->method('getValueOptions')
              ->will($this->returnValue($options));

        $element->expects($this->never())
                ->method('setValueOptions');

        $this->setProxyViaReflection($proxy, $element);
        $element->getInputSpecification();
        $this->assertEquals($options, $element->getValueOptions());
    }

    public function testGetValueOptionsDoesntInvokeProxyIfOptionsNotEmpty()
    {
        $options = ['foo' => 'bar'];

        $proxy = $this->getMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->once())
              ->method('getValueOptions')
              ->will($this->returnValue($options));

        $this->setProxyViaReflection($proxy);

        $this->assertEquals($options, $this->element->getValueOptions());
        $this->assertEquals($options, $this->element->getValueOptions());
    }

    public function testOptionsCanBeSetSingle()
    {
        $proxy = $this->getMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->once())->method('setOptions')->with(['is_method' => true]);

        $this->setProxyViaReflection($proxy);

        $this->element->setOption('is_method', true);
    }
}
