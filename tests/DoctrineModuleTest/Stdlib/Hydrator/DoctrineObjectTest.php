<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use PHPUnit_Framework_TestCase as BaseTestCase;
use ReflectionClass;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineObjectHydrator;

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
    public function setUp()
    {
        parent::setUp();

        $this->metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->hydratorByValue     = new DoctrineObjectHydrator($this->objectManager, true);
        $this->hydratorByReference = new DoctrineObjectHydrator($this->objectManager, false);
    }

    public function configureObjectManagerForSimpleEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity');

        $this->objectManager->expects($this->atLeastOnce())
                            ->method('getClassMetadata')
                            ->with($this->equalTo('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity'))
                            ->will($this->returnValue($this->metadata));

        $this->metadata->expects($this->any())
                       ->method('getFieldNames')
                       ->will($this->returnValue(array('id', 'field')));

        $this->metadata->expects($this->any())
                       ->method('getTypeOfField')
                       ->with($this->logicalOr(
                            $this->equalTo('id'),
                            $this->equalTo('field')))
                       ->will($this->returnCallback(function($arg) {
                            if ($arg === 'id') {
                                return 'integer';
                            } elseif ($arg === 'field') {
                                return 'string';
                            }
                       }));

        $this->metadata->expects($this->any())
                       ->method('hasAssociation')
                       ->with($this->logicalOr(
                            $this->equalTo('id'),
                            $this->equalTo('field')))
                       ->will($this->returnCallback(function($arg) {
                            return false;
        }));

        $this->metadata->expects($this->any())
                       ->method('getIdentifierFieldNames')
                       ->will($this->returnValue(array('id')));

        $this->metadata->expects($this->any())
                       ->method('getReflectionClass')
                       ->will($this->returnValue($refl));
    }

    public function configureObjectManagerForOneToOneEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity');

        $this->objectManager->expects($this->atLeastOnce())
                            ->method('getClassMetadata')
                            ->with($this->equalTo('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity'))
                            ->will($this->returnValue($this->metadata));

        $this->metadata->expects($this->any())
                       ->method('getFieldNames')
                       ->will($this->returnValue(array('id', 'toOne')));

        $this->metadata->expects($this->any())
                       ->method('getTypeOfField')
                       ->with($this->logicalOr(
                            $this->equalTo('id'),
                            $this->equalTo('field')))
                       ->will($this->returnCallback(function($arg) {
                                if ($arg === 'id') {
                                    return 'integer';
                                } elseif ($arg === 'toOne') {
                                    return 'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity';
                                }
                       }));

        $this->metadata->expects($this->any())
                       ->method('hasAssociation')
                       ->with($this->logicalOr(
                            $this->equalTo('id'),
                            $this->equalTo('field')))
                       ->will($this->returnCallback(function($arg) {
                                if ($arg === 'id') {
                                    return false;
                                } elseif ($arg === 'toOne') {
                                    return true;
                                }
                       }));

        $this->metadata->expects($this->any())
                       ->method('getReflectionClass')
                       ->will($this->returnValue($refl));
    }

    public function testCanExtractSimpleEntityByValue()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $entity = new Asset\SimpleEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $this->configureObjectManagerForSimpleEntity();

        $data = $this->hydratorByValue->extract($entity);
        $this->assertEquals(array('id' => 2, 'field' => 'From getter: foo'), $data);
    }

    public function testCanExtractSimpleEntityByReference()
    {
        // When using extraction by reference, it won't use the public API of entity (getters won't be called)
        $entity = new Asset\SimpleEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $this->configureObjectManagerForSimpleEntity();

        $data = $this->hydratorByReference->extract($entity);
        $this->assertEquals(array('id' => 2, 'field' => 'foo'), $data);
    }

    public function testCanHydrateSimpleEntityByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();
        $this->configureObjectManagerForSimpleEntity();
        $data = array('field' => 'foo');

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('From setter: foo', $entity->getField(false));
    }

    public function testCanHydrateSimpleEntityByReference()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();
        $this->configureObjectManagerForSimpleEntity();
        $data = array('field' => 'foo');

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('foo', $entity->getField(false));
    }

    public function testReuseExistingEntityIfDataArrayContainsIdentifier()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();

        $this->configureObjectManagerForSimpleEntity();
        $data = array('id' => 1);

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this->objectManager->expects($this->any())
                            ->method('find')
                            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', array('id' => 1))
                            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('bar', $entity->getField(false));
    }

    public function testExtractOneToOneRelationshipByValue()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $entity->setId(2);
        $entity->setToOne($toOne);

        $this->configureObjectManagerForOneToOneEntity();

        $data = $this->hydratorByValue->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $data['toOne']);
        $this->assertSame($toOne, $data['toOne']);
    }
}
