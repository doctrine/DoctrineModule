<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Form\Element;

use DoctrineModule\Form\Element\ObjectMultiCheckbox;
use Laminas\Form\Element;

use function get_class;

/**
 * Tests for the ObjectMultiCheckbox element
 *
 * @covers  \DoctrineModule\Form\Element\ObjectMultiCheckbox
 */
class ObjectMultiCheckboxTest extends ProxyAwareElementTestCase
{
    /** @var ObjectMultiCheckbox  */
    protected Element $element;

    /**
     * {@inheritDoc}.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->element = new ObjectMultiCheckbox();

        $this->prepareProxy();
    }

    public function testSetValueWithCollection(): void
    {
        $this->element->setValue(
            $this->values
        );

        $this->assertEquals(
            [1, 2],
            $this->element->getValue()
        );
    }

    public function testSetValueWithArray(): void
    {
        $this->element->setValue(
            $this->values->toArray()
        );

        $this->assertEquals(
            [1, 2],
            $this->element->getValue()
        );
    }

    public function testGetValueOptionsDoesntCauseInfiniteLoopIfProxyReturnsEmptyArrayAndValidatorIsInitialized(): void
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

    public function testGetValueOptionsDoesntInvokeProxyIfOptionsNotEmpty(): void
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

    public function testOptionsCanBeSetSingle(): void
    {
        $proxy = $this->createMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->once())->method('setOptions')->with(['is_method' => true]);

        $this->setProxyViaReflection($proxy);

        $this->element->setOption('is_method', true);
    }
}
