<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Form\Element;

use DoctrineModule\Form\Element\ObjectMultiCheckbox;
use DoctrineModule\Form\Element\Proxy;
use Laminas\Form\Element;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests for the ObjectMultiCheckbox element
 */
#[CoversClass(ObjectMultiCheckbox::class)]
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

    #[Test]
    public function testSetValueWithCollection(): void
    {
        $this->element->setValue(
            $this->values,
        );

        $this->assertEquals(
            [1, 2],
            $this->element->getValue(),
        );
    }

    #[Test]
    public function testSetValueWithArray(): void
    {
        $this->element->setValue(
            $this->values->toArray(),
        );

        $this->assertEquals(
            [1, 2],
            $this->element->getValue(),
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function testOptionsCanBeSetSingle(): void
    {
        $proxy = $this->createMock(Proxy::class);
        $proxy->expects($this->once())->method('setOptions')->with(['is_method' => true]);

        $this->setProxyViaReflection($proxy);

        $this->element->setOption('is_method', true);
    }
}
