<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use Doctrine\Tests\Common\Persistence\Mapping\TestEntity;
use PHPUnit_Framework_TestCase as BaseTestCase;
use ReflectionClass;
use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineObjectHydrator;
use DoctrineModule\Stdlib\Hydrator\Strategy;

class DoctrineObjectTest extends BaseTestCase
{
    /**
     * @var array
     */
    protected $metadata = array();

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

        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->objectManager->expects($this->any())
                            ->method('getClassMetadata')
                            ->will($this->returnCallback(function($arg) {
                                return $this->metadata[$arg];
                            }));
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        $this->metadata = array();
    }

    public function testCanExtractSimpleEntityByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $entity = new Asset\SimpleEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $data = $this->getHydratorByValue($entity)->extract($entity);
        $this->assertEquals(array('id' => 2, 'field' => 'From getter: foo'), $data);
    }

    public function testCanExtractSimpleEntityByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        // When using extraction by reference, it won't use the public API of entity (getters won't be called)
        $entity = new Asset\SimpleEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $data = $this->getHydratorByReference($entity)->extract($entity);
        $this->assertEquals(array('id' => 2, 'field' => 'foo'), $data);
    }

    public function testCanHydrateSimpleEntityByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();
        $data = array('field' => 'foo');

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('From setter: foo', $entity->getField(false));
    }

    public function testCanHydrateSimpleEntityByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();
        $data = array('field' => 'foo');

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('foo', $entity->getField(false));
    }

    public function testReuseExistingEntityIfDataArrayContainsIdentifier()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();
        $data = array('id' => 1);

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', array('id' => 1))
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('bar', $entity->getField(false));
    }

    public function testExtractOneToOneAssociationByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $entity->setId(2);
        $entity->setToOne($toOne);

        $data = $this->getHydratorByValue($entity)->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $data['toOne']);
        $this->assertEquals('Modified from getToOne getter', $data['toOne']->getField(false));
        $this->assertSame($toOne, $data['toOne']);
    }

    public function testExtractOneToOneAssociationByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();
        $entity->setId(2);
        $entity->setToOne($toOne, false);

        $data = $this->getHydratorByReference($entity)->extract($entity);

        $this->assertEquals(2, $data['id']);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $data['toOne']);
        $this->assertEquals('foo', $data['toOne']->getField(false));
        $this->assertSame($toOne, $data['toOne']);
    }

    public function testHydrateOneToOneAssociationByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();

        $data = array('toOne' => $toOne);

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertEquals('Modified from setToOne setter', $entity->getToOne(false)->getField(false));
    }

    public function testHydrateOneToOneAssociationByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toOne = new Asset\SimpleEntity();
        $toOne->setId(2);
        $toOne->setField('foo', false);

        $entity = new Asset\OneToOneEntity();

        $data = array('toOne' => $toOne);

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertEquals('foo', $entity->getToOne(false)->getField(false));
    }

    public function testHydrateOneToOneAssociationByValueUsingIdentifierForRelation()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();

        // Use entity of id 1 as relation
        $data = array('toOne' => 1);

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', 1)
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testHydrateOneToOneAssociationByReferenceUsingIdentifierForRelation()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();

        // Use entity of id 1 as relation
        $data = array('toOne' => 1);

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', 1)
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testHydrateOneToOneAssociationByValueUsingIdentifierArrayForRelation()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();

        // Use entity of id 1 as relation
        $data = array('toOne' => array('id' => 1));

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', array('id' => 1))
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testHydrateOneToOneAssociationByReferenceUsingIdentifierArrayForRelation()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToOneEntity();

        // Use entity of id 1 as relation
        $data = array('toOne' => array('id' => 1));

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->once())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', array('id' => 1))
            ->will($this->returnValue($entityInDatabaseWithIdOfOne));

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity', $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity->getToOne(false));
        $this->assertSame($entityInDatabaseWithIdOfOne, $entity->getToOne(false));
    }

    public function testCanHydrateOneToOneAssociationByValueWithNullableRelation()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by value, it will use the public API of the entity to retrieve values (setters)
        $entity = new Asset\OneToOneEntity();

        $data = array('toOne' => null);

        $this->objectManager->getClassMetadata(get_class($entity))->expects($this->once())
                       ->method('hasAssociation');

        $object = $this->getHydratorByValue($entity)->hydrate($data, $entity);
        $this->assertNull($object->getToOne(false));
    }

    public function testCanHydrateOneToOneAssociationByReferenceWithNullableRelation()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        // When using hydration by reference, it won't use the public API of the entity to retrieve values (setters)
        $entity = new Asset\OneToOneEntity();

        $data = array('toOne' => null);

        $this->objectManager->getClassMetadata(get_class($entity))->expects($this->once())
                       ->method('hasAssociation');

        $object = $this->getHydratorByReference($entity)->hydrate($data, $entity);
        $this->assertNull($object->getToOne(false));
    }

    public function testExtractOneToManyAssociationByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using extraction by value, it will use the public API of the entity to retrieve values (getters)
        $toManyOne = new Asset\SimpleEntity();
        $toManyOne->setId(2);
        $toManyOne->setField('foo', false);

        $toManyTwo = new Asset\SimpleEntity();
        $toManyTwo->setId(3);
        $toManyTwo->setField('bar', false);

        $entity = new Asset\OneToManyEntity();
        $entity->setId(4);
        $entity->addEntitie($toManyOne);
        $entity->addEntitie($toManyTwo);

        $data = $this->getHydratorByValue($entity)->extract($entity);

        $this->assertEquals(4, $data['id']);
        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $data['entities']);

        $this->assertEquals($toManyOne->getId(), $data['entities'][0]->getId());
        $this->assertSame($toManyOne, $data['entities'][0]);
        $this->assertEquals($toManyTwo->getId(), $data['entities'][1]->getId());
        $this->assertSame($toManyTwo, $data['entities'][1]);
    }

    public function testExtractOneToManyAssociationByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using extraction by reference, it won't use the public API of the entity to retrieve values (getters)
        $toManyOne = new Asset\SimpleEntity();
        $toManyOne->setId(2);
        $toManyOne->setField('foo', false);

        $toManyTwo = new Asset\SimpleEntity();
        $toManyTwo->setId(3);
        $toManyTwo->setField('bar', false);

        $entity = new Asset\OneToManyEntity();
        $entity->setId(4);
        $entity->addEntitie($toManyOne);
        $entity->addEntitie($toManyTwo);

        $data = $this->getHydratorByReference($entity)->extract($entity);

        $this->assertEquals(4, $data['id']);
        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $data['entities']);

        $this->assertEquals($toManyOne->getId(), $data['entities'][0]->getId());
        $this->assertSame($toManyOne, $data['entities'][0]);
        $this->assertEquals($toManyTwo->getId(), $data['entities'][1]->getId());
        $this->assertSame($toManyTwo, $data['entities'][1]);
    }

    public function testHydrateOneToManyAssociationByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toManyOne = new Asset\SimpleEntity();
        $toManyOne->setId(2);
        $toManyOne->setField('foo', false);

        $toManyTwo = new Asset\SimpleEntity();
        $toManyTwo->setId(3);
        $toManyTwo->setField('bar', false);

        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                $toManyOne,
                $toManyTwo,
            )
        );

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toManyOne, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toManyTwo, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByValueAsArray()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toManyOne = array(
            'id' => 1,
            'field' => 'foo'
        );

        $toManyTwo = array(
            'id' => null,
            'field' => 'bar'
        );

        $toManyThree = array(
            'field' => 'baz'
        );

        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                $toManyOne,
                $toManyTwo,
                $toManyThree,
            )
        );

        $entityInDatabase = new Asset\SimpleEntity();
        $entityInDatabase->setId(1);
        $entityInDatabase->setField('foo', false);

        $this
            ->objectManager
            ->expects($this->exactly(1))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity',
                $this->equalTo(array(
                    'id' => 1
                ))
            )
            ->will($this->returnCallback(
                    function($target, $arg) use ($entityInDatabase) {
                        if ($arg['id'] === 1) {
                            return $entityInDatabase;
                        }
                    }
                ));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(1, $entities[0]->getId());
        $this->assertSame($entityInDatabase, $entities[0]);

        $this->assertNull($entities[1]->getId());
        $this->assertNull($entities[2]->getId());
    }

    public function testHydrateOneToManyAssociationByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toManyOne = new Asset\SimpleEntity();
        $toManyOne->setId(2);
        $toManyOne->setField('foo', false);

        $toManyTwo = new Asset\SimpleEntity();
        $toManyTwo->setId(3);
        $toManyTwo->setField('bar', false);

        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                $toManyOne,
                $toManyTwo
            )
        );

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toManyOne, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toManyTwo, $entities[1]);
    }

    public function testHydrateOneToManyReferenceByValueAsArray()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toManyOne = array(
            'id' => 1,
            'field' => 'foo'
        );

        $toManyTwo = array(
            'id' => 2,
            'field' => 'bar'
        );

        $toManyThree = array(
            'field' => 'baz'
        );

        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                $toManyOne,
                $toManyTwo,
                $toManyThree,
            )
        );

        $entityInDatabaseWithIdOfOne = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfOne->setId(1);
        $entityInDatabaseWithIdOfOne->setField('foo', false);

        $entityInDatabaseWithIdOfTwo = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity',
                $this->logicalOr(
                    $this->equalTo(array(
                        'id' => 1
                    )),
                    $this->equalTo(array(
                        'id' => 2
                    ))
                )
            )
            ->will($this->returnCallback(
                    function($target, $arg) use ($entityInDatabaseWithIdOfOne, $entityInDatabaseWithIdOfTwo) {
                        if ($arg['id'] === 1) {
                            return $entityInDatabaseWithIdOfOne;
                        } elseif ($arg['id'] === 2) {
                            return $entityInDatabaseWithIdOfTwo;
                        }
                    }
                ));

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(1, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfOne, $entities[0]);

        $this->assertEquals(2, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[1]);

        $this->assertNull($entities[2]->getId());
        $this->assertEquals('From setter: baz', $entities[2]->getField(false));
    }

    public function testHydrateOneToManyAssociationByValueUsingIdentifiersForRelations()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                2, 3
            )
        );

        $entityInDatabaseWithIdOfTwo = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity',
                $this->logicalOr(
                    $this->equalTo(2),
                    $this->equalTo(3)
                )
            )
            ->will($this->returnCallback(
                function($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                    if ($arg === 2) {
                        return $entityInDatabaseWithIdOfTwo;
                    } elseif ($arg === 3) {
                        return $entityInDatabaseWithIdOfThree;
                    }
                }
            ));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByValueUsingIdentifiersArrayForRelations()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                array('id' => 2),
                array('id' => 3)
            )
        );

        $entityInDatabaseWithIdOfTwo = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity',
                $this->logicalOr(
                    $this->equalTo(array('id' => 2)),
                    $this->equalTo(array('id' => 3))
                )
            )
            ->will($this->returnCallback(
                function($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                    if ($arg['id'] === 2) {
                        return $entityInDatabaseWithIdOfTwo;
                    } elseif ($arg['id'] === 3) {
                        return $entityInDatabaseWithIdOfThree;
                    }
                }
            ));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        /* @var $entity \DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity */
        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());
            $this->assertContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByReferenceUsingIdentifiersArrayForRelations()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                array('id' => 2),
                array('id' => 3)
            )
        );

        $entityInDatabaseWithIdOfTwo = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->exactly(2))
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity',
                $this->logicalOr(
                    $this->equalTo(array('id' => 2)),
                    $this->equalTo(array('id' => 3))
                )
            )
            ->will($this->returnCallback(
                function($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                    if ($arg['id'] === 2) {
                        return $entityInDatabaseWithIdOfTwo;
                    } elseif ($arg['id'] === 3) {
                        return $entityInDatabaseWithIdOfThree;
                    }
                }
            ));

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByReferenceUsingIdentifiersForRelations()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                2, 3
            )
        );

        $entityInDatabaseWithIdOfTwo = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        $this
            ->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity',
                $this->logicalOr(
                    $this->equalTo(2),
                    $this->equalTo(3)
                )
            )
            ->will($this->returnCallback(
                function($target, $arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                    if ($arg === 2) {
                        return $entityInDatabaseWithIdOfTwo;
                    } elseif ($arg === 3) {
                        return $entityInDatabaseWithIdOfThree;
                    }
                }
            ));

        $entity = $this->getHydratorByReference($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());
            $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($entityInDatabaseWithIdOfTwo, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($entityInDatabaseWithIdOfThree, $entities[1]);
    }

    public function testHydrateOneToManyAssociationByValueUsingDisallowRemoveStrategy()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $toManyOne = new Asset\SimpleEntity();
        $toManyOne->setId(2);
        $toManyOne->setField('foo', false);

        $toManyTwo = new Asset\SimpleEntity();
        $toManyTwo->setId(3);
        $toManyTwo->setField('bar', false);

        $toMany3 = new Asset\SimpleEntity();
        $toMany3->setId(8);
        $toMany3->setField('baz', false);

        $entity = new Asset\OneToManyEntity();

        // Initally add two elements
        $entity->addEntitie($toManyOne);
        $entity->addEntitie($toManyTwo);

        // The hydrated collection contains two other elements, one of them is new, and one of them is missing
        // in the new strategy
        $data = array(
            'entities' => array(
                $toManyTwo, $toMany3
            )
        );

        // Use a DisallowRemove strategy
        $hydrator = $this->getHydratorByValue($entity);
        $hydrator->addStrategy('entities', new Strategy\DisallowRemoveByValue());

        $entity = $hydrator->hydrate($data, $entity);

        $entities = $entity->getEntities(false);

        // DisallowStrategy should not remove existing entities in Collection even if it's not in the new collection
        $this->assertEquals(3, count($entities));

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toManyOne, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toManyTwo, $entities[1]);

        $this->assertEquals(8, $entities[2]->getId());
        $this->assertSame($toMany3, $entities[2]);
    }

    public function testHydrateOneToManyAssociationByReferenceUsingDisallowRemoveStrategy()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $toManyOne = new Asset\SimpleEntity();
        $toManyOne->setId(2);
        $toManyOne->setField('foo', false);

        $toManyTwo = new Asset\SimpleEntity();
        $toManyTwo->setId(3);
        $toManyTwo->setField('bar', false);

        $toMany3 = new Asset\SimpleEntity();
        $toMany3->setId(8);
        $toMany3->setField('baz', false);

        $entity = new Asset\OneToManyEntity();

        // Initally add two elements
        $entity->addEntitie($toManyOne);
        $entity->addEntitie($toManyTwo);

        // The hydrated collection contains two other elements, one of them is new, and one of them is missing
        // in the new strategy
        $data = array(
            'entities' => array(
                $toManyTwo, $toMany3
            )
        );

        // Use a DisallowRemove strategy
        $hydrator = $this->getHydratorByReference($entity);
        $hydrator->addStrategy('entities', new Strategy\DisallowRemoveByReference());
        $entity = $hydrator->hydrate($data, $entity);

        $entities = $entity->getEntities(false);

        // DisallowStrategy should not remove existing entities in Collection even if it's not in the new collection
        $this->assertEquals(3, count($entities));

        foreach ($entities as $en) {
            $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $en);
            $this->assertInternalType('integer', $en->getId());

            // Only the third element is new so the adder has not been called on it
            if ($en === $toMany3) {
                $this->assertNotContains('Modified from addEntities adder', $en->getField(false));
            }
        }

        $this->assertEquals(2, $entities[0]->getId());
        $this->assertSame($toManyOne, $entities[0]);

        $this->assertEquals(3, $entities[1]->getId());
        $this->assertSame($toManyTwo, $entities[1]);

        $this->assertEquals(8, $entities[2]->getId());
        $this->assertSame($toMany3, $entities[2]);
    }

    public function testAssertCollectionsAreNotSwappedDuringHydration()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();

        $toManyOne = new Asset\SimpleEntity();
        $toManyOne->setId(2);
        $toManyOne->setField('foo', false);

        $toManyTwo = new Asset\SimpleEntity();
        $toManyTwo->setId(3);
        $toManyTwo->setField('bar', false);

        $data = array(
            'entities' => array(
                $toManyOne, $toManyTwo
            )
        );

        // Initally add two elements
        $entity->addEntitie($toManyOne);
        $entity->addEntitie($toManyTwo);

        // Set the initial collection
        $initialCollection = $entity->getEntities(false);

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $modifiedCollection = $entity->getEntities(false);
        $this->assertSame($initialCollection, $modifiedCollection);
    }

    public function testAssertCollectionsAreNotSwappedDuringHydrationUsingIdentifiersForRelations()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                2, 3
            )
        );

        $entityInDatabaseWithIdOfTwo = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfTwo->setId(2);
        $entityInDatabaseWithIdOfTwo->setField('foo', false);

        $entityInDatabaseWithIdOfThree = new Asset\SimpleEntity();
        $entityInDatabaseWithIdOfThree->setId(3);
        $entityInDatabaseWithIdOfThree->setField('bar', false);

        // Initally add two elements
        $entity->addEntitie($entityInDatabaseWithIdOfTwo);
        $entity->addEntitie($entityInDatabaseWithIdOfThree);

        // Set the initial collection
        $initialCollection = $entity->getEntities(false);

        $this
            ->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(
                'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity',
                $this->logicalOr(
                    $this->equalTo(2),
                    $this->equalTo(3)
                )
            )
            ->will($this->returnCallback(
                function($arg) use ($entityInDatabaseWithIdOfTwo, $entityInDatabaseWithIdOfThree) {
                    if ($arg === 2) {
                        return $entityInDatabaseWithIdOfTwo;
                    } elseif ($arg === 3) {
                        return $entityInDatabaseWithIdOfThree;
                    }
                }
            ));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $modifiedCollection = $entity->getEntities(false);
        $this->assertSame($initialCollection, $modifiedCollection);
    }

    public function testCanLookupsForEmptyIdentifiers()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        // When using hydration by reference, it won't use the public API of the entity to set values (setters)
        $entity = new Asset\OneToManyEntity();

        $data = array(
            'entities' => array(
                ''
            )
        );
        $entityInDatabaseWithEmptyId = new Asset\SimpleEntity();
        $entityInDatabaseWithEmptyId->setId('');
        $entityInDatabaseWithEmptyId->setField('baz', false);

        $this
            ->objectManager
            ->expects($this->any())
            ->method('find')
            ->with('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', '')
            ->will($this->returnValue($entityInDatabaseWithEmptyId));

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity', $entity);

        $entities = $entity->getEntities(false);
        $entity = $entities[0];

        $this->assertEquals(1, count($entities));

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertSame($entityInDatabaseWithEmptyId, $entity);
    }

    public function testHandleDateTimeConversionUsingByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntityWithDateTime();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntityWithDateTime();

        $now = time();
        $data = array('date' => $now);

        $entity = $this->getHydratorByValue($entity)->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getDate());
        $this->assertEquals($now, $entity->getDate()->getTimestamp());
    }

    public function testAssertStrategiesForCollectionsAreAlwaysAddedWhenHydratorIsConstructed()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        $entity = new Asset\OneToManyEntity();

        $this->assertTrue($this->getHydratorByValue($entity)->hasStrategy('entities'));
        $this->assertTrue($this->getHydratorByReference($entity)->hasStrategy('entities'));

        $this->assertFalse($this->getHydratorByValue($entity)->hasStrategy('id'));
        $this->assertFalse($this->getHydratorByReference($entity)->hasStrategy('id'));
    }

    public function testAssertDefaultStrategyForCollectionsIsAllowRemove()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToManyEntity();

        $entity = new Asset\OneToManyEntity();

        $hydratorByReference = $this->getHydratorByReference($entity);
        $hydratorByValue = $this->getHydratorByValue($entity);

        $this->assertInstanceOf('DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByValue', $hydratorByValue->getStrategy('entities'));
        $this->assertEquals('entities', $hydratorByValue->getStrategy('entities')->getCollectionName());

        $this->assertInstanceOf('DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByReference', $hydratorByReference->getStrategy('entities'));
        $this->assertEquals('entities', $hydratorByReference->getStrategy('entities')->getCollectionName());
    }

    public function testAssertNullValueHydratedForOneToOneWithOptionalMethodSignature()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntity();

        $entity = new Asset\OneToOneEntity();

        $data = array('toOne' => null);

        $this->objectManager->expects($this->never())
                            ->method('find');

        $object = $this->getHydratorByValue($entity)->hydrate($data, $entity);
        $this->assertNull($object->getToOne(false));
    }

    public function testAssertNullValueNotUsedAsIdentifierForOneToOneWithNonOptionalMethodSignature()
    {
        // Add metadata for used entities
        $this->addMetadataForOneToOneEntityNotNullable();

        $entity = new Asset\OneToOneEntityNotNullable();
        $entity->setToOne(new Asset\SimpleEntity());

        $data = array('toOne' => null);

        $this->objectManager->expects($this->never())
                            ->method('find');

        $object = $this->getHydratorByValue($entity)->hydrate($data, $entity);
        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $object->getToOne(false));
    }

    public function testUsesStrategyOnSimpleFieldsWhenHydratingByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();

        $data = array('field' => 'foo');

        $hydrator = $this->getHydratorByValue($entity);
        $hydrator->addStrategy('field', new Asset\SimpleStrategy());

        $entity = $hydrator->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('From setter: modified while hydrating', $entity->getField(false));
    }

    public function testUsesStrategyOnSimpleFieldsWhenHydratingByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $entity = new Asset\SimpleEntity();

        $data = array('field' => 'foo');

        $hydrator = $this->getHydratorByReference($entity);
        $hydrator->addStrategy('field', new Asset\SimpleStrategy());

        $entity = $hydrator->hydrate($data, $entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals('modified while hydrating', $entity->getField(false));
    }

    public function testUsesStrategyOnSimpleFieldsWhenExtractingByValue()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        $entity = new Asset\SimpleEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $hydrator = $this->getHydratorByValue($entity);
        $hydrator->addStrategy('field', new Asset\SimpleStrategy());

        $data = $hydrator->extract($entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals(array('id' => 2, 'field' => 'modified while extracting'), $data);
    }

    public function testUsesStrategyOnSimpleFieldsWhenExtractingByReference()
    {
        // Add metadata for used entities
        $this->addMetadataForSimpleEntity();

        $entity = new Asset\SimpleEntity();
        $entity->setId(2);
        $entity->setField('foo', false);

        $hydrator = $this->getHydratorByReference($entity);
        $hydrator->addStrategy('field', new Asset\SimpleStrategy());

        $data = $hydrator->extract($entity);

        $this->assertInstanceOf('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity', $entity);
        $this->assertEquals(array('id' => 2, 'field' => 'modified while extracting'), $data);
    }

    /**
     * Get instance of hydrator
     *
     * @param  string $targetClass
     * @return \DoctrineModule\Stdlib\Hydrator\DoctrineObject
     */
    protected function getHydratorByValue($targetClass)
    {
        if(is_object($targetClass)) {
            $targetClass = get_class($targetClass);
        }

        return new DoctrineObjectHydrator($this->objectManager, $targetClass);
    }

    /**
     * Get instance of hydrator
     *
     * @param  string $targetClass
     * @return \DoctrineModule\Stdlib\Hydrator\DoctrineObject
     */
    protected function getHydratorByReference($targetClass)
    {
        if(is_object($targetClass)) {
            $targetClass = get_class($targetClass);
        }

        return new DoctrineObjectHydrator($this->objectManager, $targetClass, false);
    }

    protected function addMetadataForSimpleEntity()
    {
        $entityName = 'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity';
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $metadata->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($entityName));

        $metadata->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array()));

        $metadata->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(array('id', 'field')));

        $metadata->expects($this->any())
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

        $metadata->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $metadata->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('id')));

        $metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass($entityName)));

        $this->metadata[$entityName] = $metadata;
    }

    protected function addMetadataForSimpleEntityWithDateTime()
    {
        $entityName = 'DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntityWithDateTime';
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $metadata->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array()));

        $metadata->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(array('id', 'date')));

        $metadata->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr(
                    $this->equalTo('id'),
                    $this->equalTo('date')))
            ->will($this->returnCallback(function($arg) {
                        if ($arg === 'id') {
                            return 'integer';
                        } elseif ($arg === 'date') {
                            return 'datetime';
                        }
                    }));

        $metadata->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $metadata->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('id')));

        $metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass($entityName)));

        $this->metadata[$entityName] = $metadata;
    }

    protected function addMetadataForOneToOneEntity()
    {
        $entityName = 'DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntity';
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $metadata->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(array('id')));

        $metadata->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array('toOne')));

        $metadata->expects($this->any())
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

        $metadata->expects($this->any())
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

        $metadata->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('toOne')
            ->will($this->returnValue(true));

        $metadata->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('toOne')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity'));

        $metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass($entityName)));

        $this->metadata[$entityName] = $metadata;
    }

    protected function addMetadataForOneToOneEntityNotNullable()
    {
        $entityName = 'DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToOneEntityNotNullable';
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $metadata->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(array('id')));

        $metadata->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array('toOne')));

        $metadata->expects($this->any())
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

        $metadata->expects($this->any())
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

        $metadata->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('toOne')
            ->will($this->returnValue(true));

        $metadata->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('toOne')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity'));

        $metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass($entityName)));

        $this->metadata[$entityName] = $metadata;
    }

    protected function addMetadataForOneToManyEntity()
    {
        $entityName = 'DoctrineModuleTest\Stdlib\Hydrator\Asset\OneToManyEntity';
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $metadata->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(array('id')));

        $metadata->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array('entities')));

        $metadata->expects($this->any())
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

        $metadata->expects($this->any())
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

        $metadata->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('entities')
            ->will($this->returnValue(false));

        $metadata->expects($this->any())
            ->method('isCollectionValuedAssociation')
            ->with('entities')
            ->will($this->returnValue(true));

        $metadata->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('entities')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity'));

        $metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass($entityName)));

        $metadata->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('id')));

        $this->metadata[$entityName] = $metadata;
    }
}
