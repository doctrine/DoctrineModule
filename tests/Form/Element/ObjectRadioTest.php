<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Form\Element;

use DoctrineModule\Form\Element\ObjectRadio;
use DoctrineModule\Form\Element\Proxy;
use Laminas\Form\Element;

/**
 * Tests for the ObjectRadio element
 *
 * @covers \DoctrineModule\Form\Element\ObjectRadio
 */
class ObjectRadioTest extends ProxyAwareElementTestCase
{
    /** @var ObjectRadio  */
    protected Element $element;

    /**
     * {@inheritDoc}.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->element = new ObjectRadio();
    }

    public function testGetValueOptionsDoesntCauseInfiniteLoopIfProxyReturnsEmptyArrayAndValidatorIsInitialized(): void
    {
        $element = $this->createPartialMock($this->element::class, ['setValueOptions']);

        $options = [];

        $proxy = $this->createMock(Proxy::class);
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

        $proxy = $this->createMock(Proxy::class);
        $proxy->expects($this->once())
            ->method('getValueOptions')
            ->will($this->returnValue($options));

        $this->setProxyViaReflection($proxy);

        $this->assertEquals($options, $this->element->getValueOptions());
        $this->assertEquals($options, $this->element->getValueOptions());
    }

    public function testOptionsCanBeSetSingle(): void
    {
        $proxy = $this->createMock(Proxy::class);
        $proxy->expects($this->once())->method('setOptions')->with(['is_method' => true]);

        $this->setProxyViaReflection($proxy);

        $this->element->setOption('is_method', true);
    }
}
