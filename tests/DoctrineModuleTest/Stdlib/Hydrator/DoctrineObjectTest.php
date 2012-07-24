<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use stdClass;
use PHPUnit_Framework_TestCase as BaseTestCase;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineObjectHydrator;
use Zend\Stdlib\Hydrator\ObjectProperty as ObjectPropertyHydrator;

class DoctrineObjectTest extends BaseTestCase
{
    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected $metadata;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        parent::setUp();

        $this->metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->objectManager->expects($this->atLeastOnce())
                      ->method('getClassMetadata')
                      ->with($this->equalTo('stdClass'))
                      ->will($this->returnValue($this->metadata));

        $this->hydrator = new DoctrineObjectHydrator($this->objectManager, new ObjectPropertyHydrator());
    }

    public function testCanHydrateSimpleObject()
    {
        $data = array(
            'username' => 'foo',
            'password' => 'bar'
        );

        $this->metadata->expects($this->exactly(2))
                       ->method('getTypeOfField')
                       ->withAnyParameters()
                       ->will($this->returnValue('string'));

        $object  = $this->hydrator->hydrate($data, new stdClass());
        $extract = $this->hydrator->extract($object);

        $this->assertEquals($data, $extract);
    }

    public function testCanHydrateOneToOneEntity()
    {
        $data = array(
            'country' => 1
        );

        $this->metadata->expects($this->exactly(1))
             ->method('getTypeOfField')
             ->with($this->equalTo('country'))
             ->will($this->returnValue('integer'));

        $this->metadata->expects($this->exactly(1))
            ->method('hasAssociation')
            ->with($this->equalTo('country'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->exactly(1))
            ->method('getAssociationTargetClass')
            ->with($this->equalTo('country'))
            ->will($this->returnValue('stdClass'));

        $this->metadata->expects($this->exactly(1))
            ->method('isSingleValuedAssociation')
            ->with($this->equalTo('country'))
            ->will($this->returnValue(true));

        $country = new stdClass();
        $country->name = 'France';
        $this->objectManager->expects($this->exactly(1))
             ->method('find')
             ->will($this->returnValue($country));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertInstanceOf('stdClass', $object->country);
    }

    public function testCanHydrateOneToManyEntity()
    {
        $data = array(
            'categories' => array(
                1, 2, 3
            )
        );

        $this->metadata->expects($this->exactly(1))
            ->method('getTypeOfField')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue('array'));

        $this->metadata->expects($this->exactly(1))
            ->method('hasAssociation')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->exactly(1))
            ->method('getAssociationTargetClass')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue('stdClass'));

        $this->metadata->expects($this->exactly(1))
            ->method('isSingleValuedAssociation')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue(false));

        $this->metadata->expects($this->exactly(1))
            ->method('isCollectionValuedAssociation')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue(true));

        $categories = array();
        $categories[] = new stdClass();
        $categories[] = new stdClass();
        $categories[] = new stdClass();

        $this->objectManager->expects($this->exactly(3))
            ->method('find')
            ->will($this->returnValue(new stdClass()));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertEquals(3, count($object->categories));

        foreach ($object->categories as $category) {
            $this->assertInstanceOf('stdClass', $category);
        }
    }

    public function testHydrateHandlesDateTimeFieldsCorrectly()
    {
        $this->metadata->expects($this->exactly(2))
            ->method('getTypeOfField')
            ->with($this->equalTo('date'))
            ->will($this->returnValue('datetime'));

        // Integers
        $now    = time();
        $data   = array('date' => $now);
        $object = $this->hydrator->hydrate($data, new stdClass());

        $this->assertInstanceOf('DateTime', $object->date);
        $this->assertEquals($object->date->getTimestamp(), $now);

        // Strings
        $data   = array('date' => date('Y-m-d H:i:s', $now));
        $object = $this->hydrator->hydrate($data, new stdClass());

        $this->assertInstanceOf('DateTime', $object->date);
        $this->assertEquals($object->date->getTimestamp(), $now);
    }
}
