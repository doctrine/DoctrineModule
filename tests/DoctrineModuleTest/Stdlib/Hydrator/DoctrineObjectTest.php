<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use PHPUnit_Framework_TestCase as BaseTestCase;
use ReflectionClass;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var \Doctrine\Common\Persistence\ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectRepository;


    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->metadata         = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
    }

    public function configureObjectManagerForSimpleEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity');

        $this->metadata->expects($this->any())
                       ->method('getAssociationNames')
                       ->will($this->returnValue(array()));

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

        $this->hydratorByValue     = new DoctrineObjectHydrator($this->objectRepository, $this->metadata, true);
        $this->hydratorByReference = new DoctrineObjectHydrator($this->objectRepository, $this->metadata, false);
    }

    public function configureObjectManagerForOneToOneEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity');

        $this->metadata->expects($this->any())
                       ->method('getFieldNames')
                       ->will($this->returnValue(array('id', 'toOne')));

        $this->metadata->expects($this->any())
                       ->method('getAssociationNames')
                       ->will($this->returnValue(array('toOne')));

        $this->metadata->expects($this->any())
                       ->method('getTypeOfField')
                       ->with($this->logicalOr(
                            $this->equalTo('id'),
                            $this->equalTo('toOne')))
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
                            $this->equalTo('toOne')))
                       ->will($this->returnCallback(function($arg) {
                                if ($arg === 'id') {
                                    return false;
                                } elseif ($arg === 'toOne') {
                                    return true;
                                }
                       }));

        $this->metadata->expects($this->any())
                       ->method('isSingleValuedAssociation')
                       ->with('toOne')
                       ->will($this->returnValue(true));

        $this->metadata->expects($this->any())
                       ->method('getAssociationTargetClass')
                       ->with('toOne')
                       ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity'));

        $this->metadata->expects($this->any())
                       ->method('getReflectionClass')
                       ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator($this->objectRepository, $this->metadata, true);
        $this->hydratorByReference = new DoctrineObjectHydrator($this->objectRepository, $this->metadata, false);
    }

    public function configureObjectManagerForOneToManyEntity()
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity');

        $this->metadata->expects($this->any())
                       ->method('getFieldNames')
                       ->will($this->returnValue(array('id', 'entities')));

        $this->metadata->expects($this->any())
                       ->method('getAssociationNames')
                       ->will($this->returnValue(array('entities')));

        $this->metadata->expects($this->any())
                       ->method('getTypeOfField')
                       ->with($this->logicalOr(
                            $this->equalTo('id'),
                            $this->equalTo('entities')))
                       ->will($this->returnCallback(function($arg) {
                            if ($arg === 'id') {
                                return 'integer';
                            } elseif ($arg === 'entities') {
                                return 'Doctrine\Common\Collections\ArrayCollection';
                            }
                       }));

        $this->metadata->expects($this->any())
                       ->method('hasAssociation')
                       ->with($this->logicalOr(
                            $this->equalTo('id'),
                            $this->equalTo('entities')))
                       ->will($this->returnCallback(function($arg) {
                            if ($arg === 'id') {
                                return false;
                            } elseif ($arg === 'entities') {
                                return true;
                            }
                       }));

        $this->metadata->expects($this->any())
                       ->method('isSingleValuedAssociation')
                       ->with('entities')
                       ->will($this->returnValue(false));

        $this->metadata->expects($this->any())
                       ->method('isCollectionValuedAssociation')
                       ->with('entities')
                       ->will($this->returnValue(true));

        $this->metadata->expects($this->any())
                       ->method('getAssociationTargetClass')
                       ->with('entities')
                       ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity'));

        $this->metadata->expects($this->any())
                       ->method('getReflectionClass')
                       ->will($this->returnValue($refl));

        $this->hydratorByValue     = new DoctrineObjectHydrator($this->objectRepository, $this->metadata, true);
        $this->hydratorByReference = new DoctrineObjectHydrator($this->objectRepository, $this->metadata, false);
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

        $this->objectRepository->expects($this->once())
                               ->method('find')
                               ->with(array('id' => 1))
                               ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('bar', $entity->getField(false));
    }

    public function testExtractOneToOneAssociationByValue()
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
        $this->assertEquals('Modified from getToOne getter', $data['toOne']->getField(false));
        $this->assertSame($toOne, $data['toOne']);
    }

    public function testExtractOneToOneAssociationByReference()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $entity->setId(2);
        $entity->setToOne($toOne, false);

        $this->configureObjectManagerForOneToOneEntity();

        $data = $this->hydratorByReference->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $data['toOne']);
        $this->assertEquals('foo', $data['toOne']->getField(false));
        $this->assertSame($toOne, $data['toOne']);
    }

    public function testHydrateOneToOneAssociationByValue()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        $data = array('toOne' => $toOne);

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertEquals('Modified from setToOne setter', $entity->getToOne(false)->getField(false));
    }

    public function testHydrateOneToOneAssociationByReference()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        $data = array('toOne' => $toOne);

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertEquals('foo', $entity->getToOne(false)->getField(false));
    }

    public function testHydrateOneToOneAssociationByValueUsingIdentifierForRelation()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        // Use entity of id 1 as relation
        $data = array('toOne' => 1);

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this->objectRepository->expects($this->once())
                               ->method('find')
                               ->with(1)
                               ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testHydrateOneToOneAssociationByReferenceUsingIdentifierForRelation()
    {
        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();
        $this->configureObjectManagerForOneToOneEntity();

        // Use entity of id 1 as relation
        $data = array('toOne' => 1);

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this->objectRepository->expects($this->once())
                               ->method('find')
                               ->with(1)
                               ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testCanHydrateOneToOneAssociationByValueWithNullableRelation()
    {

    }

    public function testCanHydrateOneToOneAssociationByReferenceWithNullableRelation()
    {

    }

    public function testExtractOneToManyAssociationByValue()
    {
        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toMany1 = new Asset\SimpleEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\SimpleEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $collection = new ArrayCollection(array($toMany1, $toMany2));

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

    public function testExtractOneToManyAssociationByReference()
    {
        // When using extraction by reference, it won't use the public API of the entity to retrieve values (getters)
        $toMany1 = new Asset\SimpleEntity();
        $toMany1->setId(2);
        $toMany1->setField('foo', false);

        $toMany2 = new Asset\SimpleEntity();
        $toMany2->setId(3);
        $toMany2->setField('bar', false);

        $collection = new ArrayCollection(array($toMany1, $toMany2));

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

    public function testHydrateOneToManyAssociationByValue()
    {

    }

    public function testHydrateOneToManyAssociationByReference()
    {

    }

    public function testHydrateOneToManyAssociationByValueUsingIdentifiersForRelations()
    {

    }

    public function testHydrateOneToManyAssociationByReferenceUsingIdentifiersForRelations()
    {

    }

    public function testHydrateOneToManyAssociationByValueUsingDisallowRemoveStrategy()
    {

    }

    public function testHydrateOneToManyAssociationByReferenceUsingDisallowRemoveStrategy()
    {

    }

    public function testAssertCollectionsAreNotSwappedDuringHydration()
    {

    }

    public function testHandleDateTimeConversion()
    {

    }

    public function testCanReturnOnlyIndexOfAssocationWhenExtractingForUsingInSelect()
    {

    }
}
