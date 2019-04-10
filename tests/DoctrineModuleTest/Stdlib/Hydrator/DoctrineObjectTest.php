<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineObjectHydrator;
use DoctrineModule\Stdlib\Hydrator\Filter;
use DoctrineModule\Stdlib\Hydrator\Strategy;
use DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity;
use DoctrineModuleTest\Stdlib\Hydrator\Asset\ContextStrategy;
use DoctrineModuleTest\Stdlib\Hydrator\Asset\NamingStrategyEntity;
use DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyReferencingIdentifierEntityReferencingBack;
use DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneReferencingIdentifierEntity;
use DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Prophecy\Argument;
use ReflectionClass;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Zend\Hydrator\Strategy\StrategyInterface;

class DoctrineObjectTest extends BaseTestCase
{
    /**
     * @var DoctrineObjectHydrator
     */
    protected $hydratorByValue;

    /**
     * @var DoctrineObjectHydrator
     */
    protected $hydratorByReference;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * setUp
     */
    protected function setUp()
    {
        parent::setUp();

        $this->metadata      = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

        $this->objectManager->expects($this->any())
                            ->method('getClassMetadata')
                            ->will($this->returnValue($this->metadata));
    }

    public function configureObjectManagerForSimpleEntity(string $className = SimpleEntity::class)
    {
        $refl = new ReflectionClass($className);

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($className));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'field']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('field')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ('id' === $arg) {
                            return 'integer';
                        } elseif ('field' === $arg) {
                            return 'string';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForByValueDifferentiatorEntity()
    {
        $this->configureObjectManagerForSimpleEntity(ByValueDifferentiatorEntity::class);
    }

    public function configureObjectManagerForNamingStrategyEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\NamingStrategyEntity');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\NamingStrategyEntity'));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['camelCase']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->equalTo('camelCase'))
            ->will($this->returnValue('string'));

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['camelCase']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForSimpleIsEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleIsEntity');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleIsEntity'));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'done']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('done')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ('id' === $arg) {
                            return 'integer';
                        } elseif ('done' === $arg) {
                            return 'boolean';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForSimpleEntityWithIsBoolean()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntityWithIsBoolean');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntityWithIsBoolean'));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'isActive']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('isActive')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ('id' === $arg) {
                            return 'integer';
                        } elseif ('isActive' === $arg) {
                            return 'boolean';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForSimpleEntityWithStringId(string $className = SimpleEntity::class)
    {
        $refl = new ReflectionClass($className);

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($className));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'field']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('field')))
            ->will($this->returnValue('string'));

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForByValueDifferentiatorEntityWithStringId()
    {
        $this->configureObjectManagerForSimpleEntityWithStringId(ByValueDifferentiatorEntity::class);
    }

    public function configureObjectManagerForSimpleEntityWithDateTime()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntityWithDateTime');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'date']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('date')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return 'integer';
                        } elseif ($arg === 'date') {
                            return 'datetime';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForOneToOneEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(['toOne']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('toOne')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return 'integer';
                        } elseif ($arg === 'toOne') {
                            return 'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('toOne')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return false;
                        } elseif ($arg === 'toOne') {
                            return true;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('toOne')
            ->will($this->returnValue(true));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('toOne')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity'));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(["id"]));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForOneToOneEntityNotNullable()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntityNotNullable');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(['toOne']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with(
                $this->logicalOr(
                    $this->equalTo('id'),
                    $this->equalTo('toOne'),
                    $this->equalTo('field')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return 'integer';
                        } elseif ($arg === 'toOne') {
                            return 'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity';
                        } elseif ($arg === 'field') {
                            return 'string';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->with(
                $this->logicalOr(
                    $this->equalTo('id'),
                    $this->equalTo('toOne'),
                    $this->equalTo('field')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id' || $arg === 'field') {
                            return false;
                        } elseif ($arg === 'toOne') {
                            return true;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('toOne')
            ->will($this->returnValue(true));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('toOne')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity'));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(["id"]));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForOneToManyEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(['entities']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with(
                $this->logicalOr(
                    $this->equalTo('id'),
                    $this->equalTo('entities'),
                    $this->equalTo('field')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return 'integer';
                        } elseif ($arg === 'field') {
                            return 'string';
                        } elseif ($arg === 'entities') {
                            return 'Doctrine\Common\Collections\ArrayCollection';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('entities'), $this->equalTo('field')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return false;
                        } elseif ($arg === 'field') {
                            return false;
                        } elseif ($arg === 'entities') {
                            return true;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('entities')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('isCollectionValuedAssociation')
            ->with('entities')
            ->will($this->returnValue(true));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('entities')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity'));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->metadata
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(["id"]));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function configureObjectManagerForOneToManyArrayEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyArrayEntity');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(['entities']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with(
                $this->logicalOr(
                    $this->equalTo('id'),
                    $this->equalTo('entities'),
                    $this->equalTo('field')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return 'integer';
                        } elseif ($arg === 'field') {
                            return 'string';
                        } elseif ($arg === 'entities') {
                            return 'Doctrine\Common\Collections\ArrayCollection';
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('entities')))
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        if ($arg === 'id') {
                            return false;
                        } elseif ($arg === 'field') {
                            return 'string';
                        } elseif ($arg === 'entities') {
                            return true;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $this
            ->metadata
            ->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('entities')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('isCollectionValuedAssociation')
            ->with('entities')
            ->will($this->returnValue(true));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('entities')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity'));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->metadata
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(["id"]));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function testObjectIsPassedForContextToStrategies()
    {
        $entity = new Asset\SimpleEntity();
        $entity->setId(2);
        $entity->setField('foo');

        $this->configureObjectManagerForSimpleEntityWithStringId();

        $hydrator = $this->hydratorByValue;
        $entity   = $hydrator->hydrate(['id' => 3, 'field' => 'bar'], $entity);
        $this->assertEquals(['id' => 3, 'field' => 'bar'], $hydrator->extract($entity));

        $hydrator->addStrategy('id', new ContextStrategy());
        $entity = $hydrator->hydrate(['id' => '3', 'field' => 'bar'], $entity);
        $this->assertEquals('3bar', $entity->getId());
        $this->assertEquals(['id' => '3barbar', 'field' => 'bar'], $hydrator->extract($entity));
    }

    public function testCanExtractSimpleEntityByValue()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $entity = new Asset\ByValueDifferentiatorEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $this->configureObjectManagerForByValueDifferentiatorEntity();

        $data = $this->hydratorByValue->extract($entity);
        $this->assertEquals(['id' => 2, 'field' => 'From getter: foo'], $data);
    }

    public function testCanExtractSimpleEntityByReference()
    {
        // When using extraction by reference, it won't use the public API of entity (getters won't be called)
        $entity = new Asset\ByValueDifferentiatorEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $this->configureObjectManagerForByValueDifferentiatorEntity();

        $data = $this->hydratorByReference->extract($entity);
        $this->assertEquals(['id' => 2, 'field' => 'foo'], $data);
    }

    public function testCanHydrateSimpleEntityByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\ByValueDifferentiatorEntity();
        $this->configureObjectManagerForByValueDifferentiatorEntity();
        $data = ['field' => 'foo'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('From setter: foo', $entity->getField(false));
    }

    /**
     * When using hydration by value, it will use the public API of the entity to set values (setters)
     *
     * @covers \DoctrineModule\Stdlib\Hydrator\DoctrineObject::hydrateByValue
     */
    public function testCanHydrateSimpleEntityWithStringIdByValue()
    {
        $entity = new Asset\ByValueDifferentiatorEntity();
        $data   = ['id' => 'bar', 'field' => 'foo'];

        $this->configureObjectManagerForByValueDifferentiatorEntityWithStringId();

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('From setter: foo', $entity->getField(false));
    }

    public function testCanHydrateSimpleEntityByReference()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\ByValueDifferentiatorEntity();
        $this->configureObjectManagerForByValueDifferentiatorEntity();
        $data = ['field' => 'foo'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('foo', $entity->getField(false));
    }

    /**
     * When using hydration by reference, it won't use the public API of the entity to set values (getters)
     *
     * @covers \DoctrineModule\Stdlib\Hydrator\DoctrineObject::hydrateByReference
     */
    public function testCanHydrateSimpleEntityWithStringIdByReference()
    {
        $entity = new Asset\ByValueDifferentiatorEntity();
        $data   = ['id' => 'bar', 'field' => 'foo'];

        $this->configureObjectManagerForByValueDifferentiatorEntityWithStringId();

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('foo', $entity->getField(false));
    }

    public function testReuseExistingEntityIfDataArrayContainsIdentifier()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\ByValueDifferentiatorEntity();

        $this->configureObjectManagerForByValueDifferentiatorEntity();
        $data = ['id' => 1];

        $entityInDatabaseWithIdOfOne = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', ['id' => 1])
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('bar', $entity->getField(false));
    }

    /**
     * Test for https://github.com/doctrine/DoctrineModule/issues/456
     */
    public function testReuseExistingEntityIfDataArrayContainsIdentifierWithZeroIdentifier()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\ByValueDifferentiatorEntity();

        $this->configureObjectManagerForByValueDifferentiatorEntity();
        $data = ['id' => 0];

        $entityInDatabaseWithIdOfOne = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfOne->setId(0);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', ['id' => 0])
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('bar', $entity->getField(false));
    }

    public function testExtractOneToOneAssociationByValue()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toOne = new Asset\ByValueDifferentiatorEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $entity->setId(2);
        $entity->setToOne($toOne);

        $this->configureObjectManagerForOneToOneEntity();

        $data = $this->hydratorByValue->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $data['toOne']);
        $this->assertEquals('Modified from getToOne getter', $data['toOne']->getField(false));
        $this->assertSame($toOne, $data['toOne']);
    }

    public function testExtractOneToOneAssociationByReference()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toOne = new Asset\ByValueDifferentiatorEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $entity->setId(2);
        $entity->setToOne($toOne, false);

        $this->configureObjectManagerForOneToOneEntity();

        $data = $this->hydratorByReference->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $data['toOne']);
        $this->assertEquals('foo', $data['toOne']->getField(false));
        $this->assertSame($toOne, $data['toOne']);
    }

    public function testHydrateOneToOneAssociationByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toOne = new Asset\ByValueDifferentiatorEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        $data = ['toOne' => $toOne];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity->getToOne(false));
        $this->assertEquals('Modified from setToOne setter', $entity->getToOne(false)->getField(false));
    }

    public function testHydrateOneToOneAssociationByReference()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toOne = new Asset\ByValueDifferentiatorEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        $data = ['toOne' => $toOne];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity->getToOne(false));
        $this->assertEquals('foo', $entity->getToOne(false)->getField(false));
    }

    public function testHydrateOneToOneAssociationByValueUsingIdentifierForRelation()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        // Use entity of id 1 as relation
        $data = ['toOne' => 1];

        $entityInDatabaseWithIdOfOne = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', 1)
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testHydrateOneToOneAssociationByReferenceUsingIdentifierForRelation()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        // Use entity of id 1 as relation
        $data = ['toOne' => 1];

        $entityInDatabaseWithIdOfOne = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', 1)
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testHydrateOneToOneAssociationByValueUsingIdentifierArrayForRelation()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        // Use entity of id 1 as relation
        $data = ['toOne' => ['id' => 1]];

        $entityInDatabaseWithIdOfOne = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', ['id' => 1])
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testHydrateOneToOneAssociationByValueUsingFullArrayForRelation()
    {
        $entity = new Asset\OneToOneEntityNotNullable;
        $this->configureObjectManagerForOneToOneEntityNotNullable();

        // Use entity of id 1 as relation
        $data = ['toOne' => ['id' => 1, 'field' => 'foo']];

        $entityInDatabaseWithIdOfOne = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                ['id' => 1]
            )
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf(
            'DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntityNotNullable',
            $entity
        );
        $this->assertInstanceOf(
            'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
            $entity->getToOne(false)
        );
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
        $this->assertEquals(
            'From getter: Modified from setToOne setter',
            $entityInDatabaseWithIdOfOne->getField()
        );
    }

    public function testHydrateOneToOneAssociationByReferenceUsingIdentifierArrayForRelation()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        // Use entity of id 1 as relation
        $data = ['toOne' => ['id' => 1]];

        $entityInDatabaseWithIdOfOne = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', ['id' => 1])
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testCanHydrateOneToOneAssociationByValueWithNullableRelation()
    {
        // When using hydration by value, it will use the public API of the entity to retrieve values (setters)
        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        $data = ['toOne' => null];

        $this->metadata->expects($this->atLeastOnce())
                       ->method('hasAssociation');

        $object = $this->hydratorByValue->hydrate($data, $entity);
        $this->assertNull($object->getToOne(false));
    }

    public function testCanHydrateOneToOneAssociationByReferenceWithNullableRelation()
    {
        // When using hydration by reference, it won't use the public API of the entity to retrieve values (setters)
        $entity = new Asset\OneToOneEntity();

        $this->configureObjectManagerForOneToOneEntity();
        $this->objectManager->expects($this->never())->method('find');
        $this->metadata->expects($this->atLeastOnce())->method('hasAssociation');

        $data = ['toOne' => null];

        $object = $this->hydratorByReference->hydrate($data, $entity);
        $this->assertNull($object->getToOne(false));
    }

    public function testExtractOneToManyAssociationByValue()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $collection = new ArrayCollection([$toMany1, $toMany2]);

        $entity = new Asset\OneToManyEntity();
        $entity->setId(4);
        $entity->addEntities($collection);

        $this->configureObjectManagerForOneToManyEntity();

        $data = $this->hydratorByValue->extract($entity);

        $this->assertEquals(4, $data['id']);
        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $data['entities']);

        $this->assertEquals($toMany1->getId(), $data['entities'][0]->getId());
        $this->assertSame($toMany1, $data['entities'][0]);
        $this->assertEquals($toMany2->getId(), $data['entities'][1]->getId());
        $this->assertSame($toMany2, $data['entities'][1]);
    }

    /**
     * @depends testExtractOneToManyAssociationByValue
     */
    public function testExtractOneToManyByValueWithArray()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $collection = new ArrayCollection([$toMany1, $toMany2]);

        $entity = new Asset\OneToManyArrayEntity();
        $entity->setId(4);
        $entity->addEntities($collection);

        $this->configureObjectManagerForOneToManyArrayEntity();

        $data = $this->hydratorByValue->extract($entity);

        $this->assertEquals(4, $data['id']);
        $this->assertIsArray($data['entities']);

        $this->assertEquals($toMany1->getId(), $data['entities'][0]->getId());
        $this->assertSame($toMany1, $data['entities'][0]);
        $this->assertEquals($toMany2->getId(), $data['entities'][1]->getId());
        $this->assertSame($toMany2, $data['entities'][1]);
    }

    public function testExtractOneToManyAssociationByReference()
    {
        // When using extraction by reference, it won't use the public API of the entity to retrieve values (getters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $collection = new ArrayCollection([$toMany1, $toMany2]);

        $entity = new Asset\OneToManyEntity();
        $entity->setId(4);
        $entity->addEntities($collection);

        $this->configureObjectManagerForOneToManyEntity();

        $data = $this->hydratorByReference->extract($entity);

        $this->assertEquals(4, $data['id']);
        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $data['entities']);

        $this->assertEquals($toMany1->getId(), $data['entities'][0]->getId());
        $this->assertSame($toMany1, $data['entities'][0]);
        $this->assertEquals($toMany2->getId(), $data['entities'][1]->getId());
        $this->assertSame($toMany2, $data['entities'][1]);
    }

    /**
     * @depends testExtractOneToManyAssociationByReference
     */
    public function testExtractOneToManyArrayByReference()
    {
        // When using extraction by reference, it won't use the public API of the entity to retrieve values (getters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $collection = new ArrayCollection([$toMany1, $toMany2]);

        $entity = new Asset\OneToManyArrayEntity();
        $entity->setId(4);
        $entity->addEntities($collection);

        $this->configureObjectManagerForOneToManyArrayEntity();

        $data = $this->hydratorByReference->extract($entity);

        $this->assertEquals(4, $data['id']);
        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $data['entities']);

        $this->assertEquals($toMany1->getId(), $data['entities'][0]->getId());
        $this->assertSame($toMany1, $data['entities'][0]);
        $this->assertEquals($toMany2->getId(), $data['entities'][1]->getId());
        $this->assertSame($toMany2, $data['entities'][1]);
    }

    public function testHydrateOneToManyAssociationByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [$toMany1, $toMany2],
        ];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toMany1, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toMany2, $entities[1]);
    }

    /**
     * @depends testHydrateOneToManyAssociationByValue
     */
    public function testHydrateOneToManyArrayByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $entity = new Asset\OneToManyArrayEntity();
        $this->configureObjectManagerForOneToManyArrayEntity();

        $data = [
            'entities' => [$toMany1, $toMany2],
        ];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyArrayEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toMany1, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toMany2, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByReference()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [$toMany1, $toMany2],
        ];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toMany1, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toMany2, $entities[1]);
    }

    /**
     * @depends testHydrateOneToManyAssociationByReference
     */
    public function testHydrateOneToManyArrayByReference()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $entity = new Asset\OneToManyArrayEntity();
        $this->configureObjectManagerForOneToManyArrayEntity();

        $data = [
            'entities' => [$toMany1, $toMany2],
        ];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyArrayEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toMany1, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toMany2, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByValueUsingIdentifiersForRelations()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [2, 3],
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByValueUsingIdentifiersArrayForRelations()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [
                ['id' => 2],
                ['id' => 3],
            ],
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByReferenceUsingIdentifiersArrayForRelations()
    {

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [
                ['id' => 2],
                ['id' => 3],
            ],
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByReferenceUsingIdentifiersForRelations()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [2, 3],
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByValueUsingDisallowRemoveStrategy()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $toMany3 = new Asset\ByValueDifferentiatorEntity();
        $toMany3->setId(8);
        $toMany3->setField('baz', false);

        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        // Initially add two elements
        $entity->addEntities(new ArrayCollection([$toMany1, $toMany2]));

        // The hydrated collection contains two other elements, one of them is new, and one of them is missing
        // in the new strategy
        $data = [
            'entities' => [$toMany2, $toMany3],
        ];

        // Use a DisallowRemove strategy
        $this->hydratorByValue->addStrategy('entities', new Strategy\DisallowRemoveByValue());
        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $entities = $entity->getEntities(false);

        // DisallowStrategy should not remove existing entities in Collection even if it's not in the new collection
        $this->assertCount(3, $entities);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toMany1, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toMany2, $entities[1]);

        $this->assertEquals(8, $entities[2]->getId());
        $this->assertSame($toMany3, $entities[2]);
    }

    public function testHydrateOneToManyAssociationByReferenceUsingDisallowRemoveStrategy()
    {
       // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $toMany3 = new Asset\ByValueDifferentiatorEntity();
        $toMany3->setId(8);
        $toMany3->setField('baz', false);

        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        // Initially add two elements
        $entity->addEntities(new ArrayCollection([$toMany1, $toMany2]));

        // The hydrated collection contains two other elements, one of them is new, and one of them is missing
        // in the new strategy
        $data = [
            'entities' => [$toMany2, $toMany3],
        ];

        // Use a DisallowRemove strategy
        $this->hydratorByReference->addStrategy('entities', new Strategy\DisallowRemoveByReference());
        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $entities = $entity->getEntities(false);

        // DisallowStrategy should not remove existing entities in Collection even if it's not in the new collection
        $this->assertCount(3, $entities);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());

            // Only the third element is new so the adder has not been called on it
            if ($en === $toMany3) {
                $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
            }
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toMany1, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toMany2, $entities[1]);

        $this->assertEquals(8, $entities[2]->getId());
        $this->assertSame($toMany3, $entities[2]);
    }

    public function testHydrateOneToManyAssociationByValueWithArrayCausingDataModifications()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $data = [
            'entities' => [
                ['id' => 2, 'field' => 'Modified By Hydrate'],
                ['id' => 3, 'field' => 'Modified By Hydrate'],
            ],
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $entity = new Asset\OneToManyEntityWithEntities(
            new ArrayCollection([
                $entityInDatabaseWithIdOfTwo,
                $entityInDatabaseWithIdOfThree,
            ])
        );
        $this->configureObjectManagerForOneToManyEntity();

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntityWithEntities', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertIsString($en->getField());
            $this->assertContains('Modified By Hydrate', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }


    public function testHydrateOneToManyAssociationByValueWithTraversableCausingDataModifications()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $data = [
            'entities' => new ArrayCollection([
                ['id' => 2, 'field' => 'Modified By Hydrate'],
                ['id' => 3, 'field' => 'Modified By Hydrate'],
            ]),
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $entity = new Asset\OneToManyEntityWithEntities(
            new ArrayCollection([
                $entityInDatabaseWithIdOfTwo,
                $entityInDatabaseWithIdOfThree,
            ])
        );
        $this->configureObjectManagerForOneToManyEntity();

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntityWithEntities', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertIsString($en->getField());
            $this->assertContains('Modified By Hydrate', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByValueWithStdClass()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $stdClass1     = new \StdClass();
        $stdClass1->id = 2;

        $stdClass2     = new \StdClass();
        $stdClass2->id = 3;

        $data = ['entities' => [$stdClass1, $stdClass2]];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $entity = new Asset\OneToManyEntityWithEntities(
            new ArrayCollection([
                $entityInDatabaseWithIdOfTwo,
                $entityInDatabaseWithIdOfThree,
            ])
        );
        $this->configureObjectManagerForOneToManyEntity();

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntityWithEntities', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByReferenceWithArrayCausingDataModifications()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $data = [
            'entities' => [
                ['id' => 2, 'field' => 'Modified By Hydrate'],
                ['id' => 3, 'field' => 'Modified By Hydrate'],
            ],
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('Unmodified Value', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('Unmodified Value', false);

        $entity = new Asset\OneToManyEntityWithEntities(
            new ArrayCollection([
                $entityInDatabaseWithIdOfTwo,
                $entityInDatabaseWithIdOfThree,
            ])
        );

        $reflSteps = [
            new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntityWithEntities'),
            new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity'),
            new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity'),
            new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntityWithEntities'),
        ];
        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnCallback(
                function () use (&$reflSteps) {
                    $refl = array_shift($reflSteps);
                    return $refl;
                }
            ));

        $this->configureObjectManagerForOneToManyEntity();

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        } elseif ($arg['id'] === 3) {
                            return $entityInDatabaseWithIdOfThree;
                        }

                        throw new \InvalidArgumentException();
                    }
                )
            );

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntityWithEntities', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $en);
            $this->assertIsInt($en->getId());
            $this->assertIsString($en->getField());
            $this->assertContains('Modified By Hydrate', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testAssertCollectionsAreNotSwappedDuringHydration()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $toMany1 = new Asset\ByValueDifferentiatorEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\ByValueDifferentiatorEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $data = [
            'entities' => [$toMany1, $toMany2],
        ];

        // Set the initial collection
        $entity->addEntities(new ArrayCollection([$toMany1, $toMany2]));
        $initialCollection = $entity->getEntities(false);

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $modifiedCollection = $entity->getEntities(false);
        $this->assertSame($initialCollection, $modifiedCollection);
    }

    public function testAssertCollectionsAreNotSwappedDuringHydrationUsingIdentifiersForRelations()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [2, 3],
        ];

        $entityInDatabaseWithIdOfTwo = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        // Set the initial collection
        $entity->addEntities(new ArrayCollection([$entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree]));
        $initialCollection = $entity->getEntities(false);

        $this
            ->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity',
                $this->logicalOr($this->equalTo(['id' => 2]), $this->equalTo(['id' => 3]))
            )
            ->will(
                $this->returnCallback(
                    function ($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                        if ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        }

                        return $entityInDatabaseWithIdOfThree;
                    }
                )
            );

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $modifiedCollection = $entity->getEntities(false);
        $this->assertSame($initialCollection, $modifiedCollection);
    }

    public function testCanLookupsForEmptyIdentifiers()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();
        $this->configureObjectManagerForOneToManyEntity();

        $data = [
            'entities' => [
                '',
            ],
        ];

        $entityInDatabaseWithEmptyId = new Asset\ByValueDifferentiatorEntity();
        $entityInDatabaseWithEmptyId->setId('');
        $entityInDatabaseWithEmptyId->setField('baz', false);

        $this
            ->objectManager
            ->expects($this->any())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', '')
            ->will($this->returnValue($entityInDatabaseWithEmptyId));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);
        $entity   = $entities[0];

        $this->assertCount(1, $entities);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertSame($entityInDatabaseWithEmptyId, $entity);
    }

    public function testHandleDateTimeConversionUsingByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntityWithDateTime();
        $this->configureObjectManagerForSimpleEntityWithDateTime();

        $now  = time();
        $data = ['date' => $now];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getDate());
        $this->assertEquals($now, $entity->getDate()->getTimestamp());
    }

    public function testEmptyStringIsNotConvertedToDateTime()
    {
        $entity = new Asset\SimpleEntityWithDateTime();
        $this->configureObjectManagerForSimpleEntityWithDateTime();

        $data = ['date' => ''];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertNull($entity->getDate());
    }

    public function testAssertNullValueHydratedForOneToOneWithOptionalMethodSignature()
    {
        $entity = new Asset\OneToOneEntity();

        $this->configureObjectManagerForOneToOneEntity();
        $this->objectManager->expects($this->never())->method('find');

        $data = ['toOne' => null];


        $object = $this->hydratorByValue->hydrate($data, $entity);
        $this->assertNull($object->getToOne(false));
    }

    public function testAssertNullValueNotUsedAsIdentifierForOneToOneWithNonOptionalMethodSignature()
    {
        $entity = new Asset\OneToOneEntityNotNullable();

        $entity->setToOne(new Asset\ByValueDifferentiatorEntity());
        $this->configureObjectManagerForOneToOneEntityNotNullable();
        $this->objectManager->expects($this->never())->method('find');

        $data = ['toOne' => null];

        $object = $this->hydratorByValue->hydrate($data, $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $object->getToOne(false));
    }

    public function testUsesStrategyOnSimpleFieldsWhenHydratingByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\ByValueDifferentiatorEntity();
        $this->configureObjectManagerForByValueDifferentiatorEntity();
        $data = ['field' => 'foo'];

        $this->hydratorByValue->addStrategy('field', new Asset\SimpleStrategy());
        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('From setter: modified while hydrating', $entity->getField(false));
    }

    public function testUsesStrategyOnSimpleFieldsWhenHydratingByReference()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\ByValueDifferentiatorEntity();
        $this->configureObjectManagerForByValueDifferentiatorEntity();
        $data = ['field' => 'foo'];

        $this->hydratorByReference->addStrategy('field', new Asset\SimpleStrategy());
        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals('modified while hydrating', $entity->getField(false));
    }

    public function testUsesStrategyOnSimpleFieldsWhenExtractingByValue()
    {
        $entity = new Asset\ByValueDifferentiatorEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $this->configureObjectManagerForByValueDifferentiatorEntity();

        $this->hydratorByValue->addStrategy('field', new Asset\SimpleStrategy());
        $data = $this->hydratorByValue->extract($entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals(['id' => 2, 'field' => 'modified while extracting'], $data);
    }

    public function testUsesStrategyOnSimpleFieldsWhenExtractingByReference()
    {
        $entity = new Asset\ByValueDifferentiatorEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $this->configureObjectManagerForByValueDifferentiatorEntity();

        $this->hydratorByReference->addStrategy('field', new Asset\SimpleStrategy());
        $data = $this->hydratorByReference->extract($entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\ByValueDifferentiatorEntity', $entity);
        $this->assertEquals(['id' => 2, 'field' => 'modified while extracting'], $data);
    }

    public function testCanExtractIsserByValue()
    {
        $entity = new Asset\SimpleIsEntity();
        $entity->setId(2);
        $entity->setDone(true);

        $this->configureObjectManagerForSimpleIsEntity();

        $data = $this->hydratorByValue->extract($entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleIsEntity', $entity);
        $this->assertEquals(['id' => 2, 'done' => true], $data);
    }

    public function testCanExtractIsserThatStartsWithIsByValue()
    {
        $entity = new Asset\SimpleEntityWithIsBoolean();
        $entity->setId(2);
        $entity->setIsActive(true);

        $this->configureObjectManagerForSimpleEntityWithIsBoolean();

        $data = $this->hydratorByValue->extract($entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntityWithIsBoolean', $entity);
        $this->assertEquals(['id' => 2, 'isActive' => true], $data);
    }

    public function testExtractWithPropertyNameFilterByValue()
    {
        $entity = new Asset\ByValueDifferentiatorEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $filter = new Filter\PropertyName(['id'], false);

        $this->configureObjectManagerForByValueDifferentiatorEntity();

        $this->hydratorByValue->addFilter('propertyname', $filter);
        $data = $this->hydratorByValue->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertEquals(['id'], array_keys($data), 'Only the "id" field should have been extracted.');
    }

    public function testExtractWithPropertyNameFilterByReference()
    {
        $entity = new Asset\ByValueDifferentiatorEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $filter = new Filter\PropertyName(['id'], false);

        $this->configureObjectManagerForByValueDifferentiatorEntity();

        $this->hydratorByReference->addFilter('propertyname', $filter);
        $data = $this->hydratorByReference->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertEquals(['id'], array_keys($data), 'Only the "id" field should have been extracted.');
    }

    public function testExtractByReferenceUsesNamingStrategy()
    {
        $this->configureObjectManagerForNamingStrategyEntity();
        $name = 'Foo';
        $this->hydratorByReference->setNamingStrategy(new UnderscoreNamingStrategy());
        $data = $this->hydratorByReference->extract(new NamingStrategyEntity($name));
        $this->assertEquals($name, $data['camel_case']);
    }

    public function testExtractByValueUsesNamingStrategy()
    {
        $this->configureObjectManagerForNamingStrategyEntity();
        $name = 'Bar';
        $this->hydratorByValue->setNamingStrategy(new UnderscoreNamingStrategy());
        $data = $this->hydratorByValue->extract(new NamingStrategyEntity($name));
        $this->assertEquals($name, $data['camel_case']);
    }

    public function testHydrateByReferenceUsesNamingStrategy()
    {
        $this->configureObjectManagerForNamingStrategyEntity();
        $name = 'Baz';
        $this->hydratorByReference->setNamingStrategy(new UnderscoreNamingStrategy());
        $entity = $this->hydratorByReference->hydrate(['camel_case' => $name], new NamingStrategyEntity());
        $this->assertEquals($name, $entity->getCamelCase());
    }

    public function testHydrateByValueUsesNamingStrategy()
    {
        $this->configureObjectManagerForNamingStrategyEntity();
        $name = 'Qux';
        $this->hydratorByValue->setNamingStrategy(new UnderscoreNamingStrategy());
        $entity = $this->hydratorByValue->hydrate(['camel_case' => $name], new NamingStrategyEntity());
        $this->assertEquals($name, $entity->getCamelCase());
    }

    public function configureObjectManagerForSimplePrivateEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimplePrivateEntity');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimplePrivateEntity'));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['private', 'protected']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('private'), $this->equalTo('protected')))
            ->will($this->returnValue('integer'));

        $this
            ->metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['private']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator(
            $this->objectManager,
            true
        );
        $this->hydratorByReference = new DoctrineObjectHydrator(
            $this->objectManager,
            false
        );
    }

    public function testCannotHydratePrivateByValue()
    {
        $entity = new Asset\SimplePrivateEntity();
        $this->configureObjectManagerForSimplePrivateEntity();
        $data = ['private' => 123, 'protected' => 456];

        $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimplePrivateEntity', $entity);
    }

    public function testDefaultStrategy()
    {
        $this->configureObjectManagerForOneToManyEntity();

        $entity = new Asset\OneToManyEntity();

        $this->hydratorByValue->hydrate(array(), $entity);

        $this->assertEquals(
            'DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByValue',
            $this->hydratorByValue->getDefaultByValueStrategy()
        );

        $this->assertInstanceOf(
            'DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByValue',
            $this->hydratorByValue->getStrategy('entities')
        );

        $this->hydratorByReference->hydrate(array(), $entity);

        $this->assertEquals(
            'DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByReference',
            $this->hydratorByReference->getDefaultByReferenceStrategy()
        );

        $this->assertInstanceOf(
            'DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByReference',
            $this->hydratorByReference->getStrategy('entities')
        );
    }

    /**
     * @depends testDefaultStrategy
     */
    public function testOverrideDefaultStrategy()
    {
        $this->configureObjectManagerForOneToManyEntity();

        $this->hydratorByValue->setDefaultByValueStrategy(__NAMESPACE__ . '\Asset\DifferentAllowRemoveByValue');
        $this->hydratorByReference->setDefaultByReferenceStrategy(__NAMESPACE__ . '\Asset\DifferentAllowRemoveByReference');

        $entity = new Asset\OneToManyEntity();

        $this->hydratorByValue->hydrate(array(), $entity);

        $this->assertEquals(
            __NAMESPACE__ . '\Asset\DifferentAllowRemoveByValue',
            $this->hydratorByValue->getDefaultByValueStrategy()
        );

        $this->assertInstanceOf(
            __NAMESPACE__ . '\Asset\DifferentAllowRemoveByValue',
            $this->hydratorByValue->getStrategy('entities')
        );

        $this->hydratorByReference->hydrate(array(), $entity);

        $this->assertEquals(
            __NAMESPACE__ . '\Asset\DifferentAllowRemoveByReference',
            $this->hydratorByReference->getDefaultByReferenceStrategy()
        );

        $this->assertInstanceOf(
            __NAMESPACE__ . '\Asset\DifferentAllowRemoveByReference',
            $this->hydratorByReference->getStrategy('entities')
        );
    }

    /**
     * https://github.com/doctrine/DoctrineModule/issues/639
     */
    public function testStrategyWithArrayByValue()
    {
        $entity = new Asset\SimpleEntity();

        $data = ['field' => ['complex', 'value']];
        $this->configureObjectManagerForSimpleEntity();
        $this->hydratorByValue->addStrategy('field', new class implements StrategyInterface {
            public function extract($value) : array
            {
                return explode(',', $value);
            }

            public function hydrate($value) : string
            {
                return implode(',', $value);
            }

        });

        $this->hydratorByValue->hydrate($data, $entity);

        $this->assertEquals('complex,value', $entity->getField());
    }

    public function testStrategyWithArrayByReference()
    {
        $entity = new Asset\SimpleEntity();

        $data = ['field' => ['complex', 'value']];
        $this->configureObjectManagerForSimpleEntity();
        $this->hydratorByReference->addStrategy('field', new class implements StrategyInterface {
            public function extract($value) : array
            {
                return explode(',', $value);
            }

            public function hydrate($value) : string
            {
                return implode(',', $value);
            }

        });

        $this->hydratorByReference->hydrate($data, $entity);

        $this->assertSame('complex,value', $entity->getField());
    }

    private function getObjectManagerForNestedHydration()
    {
        $oneToOneMetadata = $this->prophesize(ClassMetadata::class);
        $oneToOneMetadata->getName()->willReturn(Asset\OneToOneEntity::class);
        $oneToOneMetadata->getFieldNames()->willReturn(['id', 'toOne', 'createdAt']);
        $oneToOneMetadata->getAssociationNames()->willReturn(['toOne']);
        $oneToOneMetadata->getTypeOfField('id')->willReturn('integer');
        $oneToOneMetadata->getTypeOfField('toOne')->willReturn(Asset\ByValueDifferentiatorEntity::class);
        $oneToOneMetadata->getTypeOfField('createdAt')->willReturn('datetime');
        $oneToOneMetadata->hasAssociation('id')->willReturn(false);
        $oneToOneMetadata->hasAssociation('toOne')->willReturn(true);
        $oneToOneMetadata->hasAssociation('createdAt')->willReturn(false);
        $oneToOneMetadata->isSingleValuedAssociation('toOne')->willReturn(true);
        $oneToOneMetadata->isCollectionValuedAssociation('toOne')->willReturn(false);
        $oneToOneMetadata->getAssociationTargetClass('toOne')->willReturn(Asset\ByValueDifferentiatorEntity::class);
        $oneToOneMetadata->getReflectionClass()->willReturn(new ReflectionClass(Asset\OneToOneEntity::class));
        $oneToOneMetadata->getIdentifier()->willReturn(['id']);
        $oneToOneMetadata->getIdentifierFieldNames(Argument::type(Asset\OneToOneEntity::class))->willReturn(['id']);

        $byValueDifferentiatorEntity = $this->prophesize(ClassMetadata::class);
        $byValueDifferentiatorEntity->getName()->willReturn(Asset\ByValueDifferentiatorEntity::class);
        $byValueDifferentiatorEntity->getAssociationNames()->willReturn([]);
        $byValueDifferentiatorEntity->getFieldNames()->willReturn(['id', 'field']);
        $byValueDifferentiatorEntity->getTypeOfField('id')->willReturn('integer');
        $byValueDifferentiatorEntity->getTypeOfField('field')->willReturn('string');
        $byValueDifferentiatorEntity->hasAssociation(Argument::any())->willReturn(false);
        $byValueDifferentiatorEntity->getIdentifier()->willReturn(['id']);
        $byValueDifferentiatorEntity->getIdentifierFieldNames(Argument::type(Asset\ByValueDifferentiatorEntity::class))->willReturn(['id']);
        $byValueDifferentiatorEntity->getReflectionClass()->willReturn(new ReflectionClass(Asset\ByValueDifferentiatorEntity::class));

        $objectManager = $this->prophesize(ObjectManager::class);
        $objectManager->getClassMetadata(Asset\OneToOneEntity::class)->will([$oneToOneMetadata, 'reveal']);
        $objectManager->getClassMetadata(Asset\ByValueDifferentiatorEntity::class)->will([$byValueDifferentiatorEntity, 'reveal']);
        $objectManager->find(Asset\OneToOneEntity::class, ['id' => 12])->willReturn(false);
        $objectManager->find(Asset\ByValueDifferentiatorEntity::class, ['id' => 13])->willReturn(false);

        return $objectManager->reveal();
    }

    public function testNestedHydrationByValue()
    {
        $objectManager = $this->getObjectManagerForNestedHydration();
        $hydrator = new DoctrineObjectHydrator($objectManager, true);
        $entity = new Asset\OneToOneEntity();

        $data = [
            'id' => 12,
            'toOne' => [
                'id' => 13,
                'field' => 'value',
            ],
            'createdAt' => '2019-01-24 12:00:00',
        ];

        $hydrator->hydrate($data, $entity);

        $this->assertSame(12, $entity->getId());
        $this->assertInstanceOf(Asset\ByValueDifferentiatorEntity::class, $entity->getToOne(false));
        $this->assertSame(13, $entity->getToOne(false)->getId());
        $this->assertSame('Modified from setToOne setter', $entity->getToOne(false)->getField(false));
        $this->assertSame('2019-01-24 12:00:00', $entity->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    public function testNestedHydrationByReference()
    {
        $objectManager = $this->getObjectManagerForNestedHydration();
        $hydrator = new DoctrineObjectHydrator($objectManager, false);
        $entity = new Asset\OneToOneEntity();

        $data = [
            'id' => 12,
            'toOne' => [
                'id' => 13,
                'field' => 'value',
            ],
            'createdAt' => '2019-01-24 12:00:00',
        ];

        $hydrator->hydrate($data, $entity);

        $this->assertSame(12, $entity->getId());
        $this->assertInstanceOf(Asset\ByValueDifferentiatorEntity::class, $entity->getToOne(false));
        $this->assertSame(13, $entity->getToOne(false)->getId());
        $this->assertSame('value', $entity->getToOne(false)->getField(false));
        $this->assertSame('2019-01-24 12:00:00', $entity->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    public function testHydrateInToManyCollectionWontOverrideMetadata()
    {
        $objectManager = $this->getObjectManagerForNestedToManyHydration();
        $hydrator = new DoctrineObjectHydrator($objectManager, false);
        $entity = new Asset\OneToManyReferencingIdentifierEntity();

        $data = [
            'toMany' => [
                [
                    'createdAt' => '2019-04-10 09:00:00',
                ],
            ],
        ];

        $hydrator->hydrate($data, $entity);
    }

    private function getObjectManagerForNestedToManyHydration()
    {
        $oneToOneMetadata = $this->prophesize(ClassMetadata::class);
        $oneToOneMetadata->getName()->willReturn(Asset\OneToManyReferencingIdentifierEntity::class);
        $oneToOneMetadata->getFieldNames()->willReturn(['id', 'toMany']);
        $oneToOneMetadata->getAssociationNames()->willReturn(['toMany']);
        $oneToOneMetadata->getTypeOfField('id')->willReturn('integer');
        $oneToOneMetadata->getTypeOfField('toMany')->willReturn(Asset\OneToManyReferencingIdentifierEntityReferencingBack::class);
        $oneToOneMetadata->hasAssociation('id')->willReturn(false);
        $oneToOneMetadata->hasAssociation('toMany')->willReturn(true);
        $oneToOneMetadata->isSingleValuedAssociation('toMany')->willReturn(false);
        $oneToOneMetadata->isCollectionValuedAssociation('toMany')->willReturn(true);
        $oneToOneMetadata->getAssociationTargetClass('toMany')->willReturn(Asset\OneToManyReferencingIdentifierEntityReferencingBack::class);
        $oneToOneMetadata->getReflectionClass()->willReturn(new ReflectionClass(Asset\OneToManyReferencingIdentifierEntity::class));
        $oneToOneMetadata->getIdentifier()->willReturn(['id']);
        $oneToOneMetadata->getIdentifierFieldNames(Argument::type(Asset\OneToManyReferencingIdentifierEntity::class))->willReturn(['id']);

        $oneToOneReferencingBackEntity = $this->prophesize(ClassMetadata::class);
        $oneToOneReferencingBackEntity->getName()->willReturn(Asset\OneToManyReferencingIdentifierEntityReferencingBack::class);
        $oneToOneReferencingBackEntity->getAssociationNames()->willReturn(['toOneReferencingBack']);
        $oneToOneReferencingBackEntity->getFieldNames()->willReturn(['toOneReferencingBack', 'secondaryCompositePrimaryKey', 'createdAt']);
        $oneToOneReferencingBackEntity->getTypeOfField('toOneReferencingBack')->willReturn(Asset\OneToManyReferencingIdentifierEntity::class);
        $oneToOneReferencingBackEntity->getTypeOfField('createdAt')->willReturn('datetime');
        $oneToOneReferencingBackEntity->getTypeOfField('secondaryCompositePrimaryKey')->willReturn('integer');
        $oneToOneReferencingBackEntity->hasAssociation('toOneReferencingBack')->willReturn(true);
        $oneToOneReferencingBackEntity->hasAssociation('createdAt')->willReturn(false);
        $oneToOneReferencingBackEntity->hasAssociation('secondaryCompositePrimaryKey')->willReturn(false);
        $oneToOneReferencingBackEntity->isSingleValuedAssociation('toOneReferencingBack')->willReturn(true);
        $oneToOneReferencingBackEntity->isCollectionValuedAssociation('toOneReferencingBack')->willReturn(false);
        $oneToOneReferencingBackEntity->getAssociationTargetClass('toOneReferencingBack')->willReturn(Asset\OneToManyReferencingIdentifierEntity::class);
        $oneToOneReferencingBackEntity->getIdentifier()->willReturn(['toOneReferencingBack', 'secondaryCompositePrimaryKey']);
        $oneToOneReferencingBackEntity->getIdentifierFieldNames(Argument::type(Asset\OneToManyReferencingIdentifierEntityReferencingBack::class))->willReturn(['toOneReferencingBack']);
        $oneToOneReferencingBackEntity->getReflectionClass()->willReturn(new ReflectionClass(Asset\OneToManyReferencingIdentifierEntityReferencingBack::class));

        $objectManager = $this->prophesize(ObjectManager::class);
        $objectManager->getClassMetadata(Asset\OneToManyReferencingIdentifierEntity::class)->will([$oneToOneMetadata, 'reveal']);
        $objectManager->getClassMetadata(Asset\OneToManyReferencingIdentifierEntityReferencingBack::class)->will([$oneToOneReferencingBackEntity, 'reveal']);
        $objectManager->find(Asset\OneToManyReferencingIdentifierEntityReferencingBack::class, ['secondaryCompositePrimaryKey' => 42])->willReturn(false);

        return $objectManager->reveal();
    }
}
