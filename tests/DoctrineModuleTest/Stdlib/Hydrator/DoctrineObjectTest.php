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
        $this->objectManager->expects($this->exactly(1))
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
            'name' => 'Paris',
            'country' => 1
        );

        $this->metadata->expects($this->exactly(2))
             ->method('getTypeOfField')
             ->withAnyParameters()
             ->will($this->returnValue('string'));

        $this->metadata->expects($this->exactly(2))
            ->method('hasAssociation')
            ->will($this->returnCallback(function() {
            $v = func_get_args();
            if ($v[0] === 'country') {
                return true;
            } else {
                return false;
            }
        }));

        $this->metadata->expects($this->exactly(1))
            ->method('getAssociationtTargetClass')
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
        //$this->assertInstanceOf('stdClass', $object->country);
    }

    public function testHydrateHandlesDateTimeFieldsCorrectly()
    {
        // Integers
        $now    = time();
        $data   = array('date' => $now);
        $entity = $this->hydrator->hydrate($data, new DateEntity());

        $this->assertInstanceOf('DateTime', $entity->getDate());
        $this->assertEquals($entity->getDate()->getTimestamp(), $now);

        // Strings
        $data   = array('date' => date('Y-m-d h:i:s'));
        $entity = $this->hydrator->hydrate($data, new DateEntity());

        $this->assertInstanceOf('DateTime', $entity->getDate());
        $this->assertEquals($entity->getDate()->getTimestamp(), $now);
    }

    public function testCanHydrateOneToOneEntity()
    {
        $data = array(
            'name' => 'Paris',
            'country' => 1
        );

        $entity = $this->hydrator->hydrate($data, new CityEntity());
        $this->assertInstanceOf('DoctrineORMModuleTest\Assets\Entity\Country', $entity->getCountry());
    }

    public function testCanHydrateOneToManyEntity()
    {
        $data = array(
            'name' => 'Chair',
            'categories' => array(
                1, 2, 3
            )
        );

        $entity = $this->hydrator->hydrate($data, new ProductEntity());
        $this->assertEquals(3, count($entity->getCategories()));

        foreach ($entity->getCategories() as $category) {
            $this->assertInstanceOf('DoctrineORMModuleTest\Assets\Entity\Category', $category);
        }
    }
}
