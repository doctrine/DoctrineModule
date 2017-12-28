<?php

namespace DoctrineModuleTest\Form\Element;

use DoctrineModule\Form\Element\ObjectRadio;

/**
 * Tests for the ObjectRadio element
 *
 * @covers  DoctrineModule\Form\Element\ObjectRadio
 */
class ObjectRadioTest extends ProxyAwareElementTestCase
{
    /**
     * @var ObjectRadio
     */
    protected $element;

    /**
     * {@inheritDoc}.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->element = new ObjectRadio();
    }

    public function testGetValueOptionsDoesntCauseInfiniteLoopIfProxyReturnsEmptyArrayAndValidatorIsInitialized()
    {
        $element = $this->createPartialMock(get_class($this->element), ['setValueOptions']);

        $options = [];

        $proxy = $this->createMock('DoctrineModule\Form\Element\Proxy');
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

        $proxy = $this->createMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->once())
            ->method('getValueOptions')
            ->will($this->returnValue($options));

        $this->setProxyViaReflection($proxy);

        $this->assertEquals($options, $this->element->getValueOptions());
        $this->assertEquals($options, $this->element->getValueOptions());
    }

    public function testOptionsCanBeSetSingle()
    {
        $proxy = $this->createMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->once())->method('setOptions')->with(['is_method' => true]);

        $this->setProxyViaReflection($proxy);

        $this->element->setOption('is_method', true);
    }
}
