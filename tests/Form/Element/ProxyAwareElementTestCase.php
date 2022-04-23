<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Form\Element;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\Mapping\ClassMetadata;
use DoctrineModuleTest\Form\Element\TestAsset\FormObject;
use Laminas\Form\Element;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

use function array_shift;
use function func_get_args;
use function get_class;
use function method_exists;

class ProxyAwareElementTestCase extends TestCase
{
    /** @var MockObject&ClassMetadata */
    protected $metadata;

    protected Element $element;

    protected ArrayCollection $values;

    protected function prepareProxy(): void
    {
        $objectClass = 'DoctrineModuleTest\Form\Element\TestAsset\FormObject';
        $objectOne   = new FormObject();
        $objectTwo   = new FormObject();

        $objectOne->setId(1)
            ->setUsername('object one username')
            ->setPassword('object one password')
            ->setEmail('object one email')
            ->setFirstname('object one firstname')
            ->setSurname('object one surname');

        $objectTwo->setId(2)
            ->setUsername('object two username')
            ->setPassword('object two password')
            ->setEmail('object two email')
            ->setFirstname('object two firstname')
            ->setSurname('object two surname');

        $result       = new ArrayCollection([$objectOne, $objectTwo]);
        $this->values = $result;

        $metadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $metadata
            ->expects($this->any())
            ->method('getIdentifierValues')
            ->will(
                $this->returnCallback(
                    static function () use ($objectOne, $objectTwo) {
                        $input = func_get_args();
                        $input = array_shift($input);

                        if ($input === $objectOne) {
                            return ['id' => 1];
                        }

                        if ($input === $objectTwo) {
                            return ['id' => 2];
                        }

                        return [];
                    }
                )
            );

        $objectRepository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $objectRepository->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($result));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($metadata));

        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($objectRepository));

        if (! method_exists($this->element, 'getProxy')) {
            throw new RuntimeException('Element must implement getProxy().');
        }

        $this->element->getProxy()->setOptions([
            'object_manager' => $objectManager,
            'target_class'   => $objectClass,
        ]);

        $this->metadata = $metadata;
    }

    /**
     * Proxy should stay read only, use with care
     */
    protected function setProxyViaReflection(MockObject $proxy, ?MockObject $element = null): void
    {
        if (! $element) {
            $element = $this->element;
        }

        $prop = new ReflectionProperty(get_class($this->element), 'proxy');
        $prop->setAccessible(true);
        $prop->setValue($element, $proxy);
    }
}
