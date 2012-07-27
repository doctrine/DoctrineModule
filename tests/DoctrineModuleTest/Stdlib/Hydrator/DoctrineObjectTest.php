<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use stdClass;
use PHPUnit_Framework_TestCase as BaseTestCase;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineObjectHydrator;
use Zend\Stdlib\Hydrator\ObjectProperty as ObjectPropertyHydrator;

class DoctrineObjectTest extends BaseTestCase
{
    /**
     * @var DoctrineObjectHydrator
     */
    protected $hydrator;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
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

    public function testCanHydrateEntityWithNullableAssociation()
    {
        $data = array(
            'country' => null
        );

        $this->metadata->expects($this->never())
                ->method('hasAssociation');

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertNull($object->country);
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

    /**
     * Tricky case: assuming the related `Review` entity has an identifier which is a `ReviewReference` object.
     */
    public function testHydrateCanFindSingleRelatedObjectByNonScalarIdentifier()
    {
        $reviewReference = new stdClass();
        $reviewReference->uuid = '1234';

        $review = new stdClass();
        $review->reviewer = 'Marco Pivetta';
        $review->description = 'Adding support for non-scalar references/identifiers';

        $data = array(
            'review' => $reviewReference,
        );

        $this->metadata->expects($this->exactly(1))
            ->method('getTypeOfField')
            ->with($this->equalTo('review'))
            ->will($this->returnValue('Review'));

        $this->metadata->expects($this->exactly(1))
            ->method('hasAssociation')
            ->with($this->equalTo('review'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->exactly(1))
            ->method('getAssociationTargetClass')
            ->with($this->equalTo('review'))
            ->will($this->returnValue('Review'));

        $this->metadata->expects($this->exactly(1))
            ->method('isSingleValuedAssociation')
            ->with($this->equalTo('review'))
            ->will($this->returnValue(true));


        $this->objectManager->expects($this->exactly(1))
            ->method('find')
            ->with('Review', $reviewReference)
            ->will($this->returnValue($review));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertSame($review, $object->review);
    }

    /**
     * Same as testHydrateCanFindSingleRelatedObjectByNonScalarIdentifier, but with collection valued associations
     */
    public function testHydrateCanFindMultipleRelatedObjectByNonScalarIdentifier()
    {
        $reviewReference = new stdClass();
        $reviewReference->uuid = '5678';

        $review = new stdClass();
        $review->reviewer = 'Marco Pivetta';
        $review->description = 'Adding support for non-scalar references/identifiers';

        $data = array(
            'reviews' => array(
                $reviewReference,
                $reviewReference,
                $reviewReference,
            ),
        );

        $this->metadata->expects($this->exactly(1))
            ->method('getTypeOfField')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue('Review'));

        $this->metadata->expects($this->exactly(1))
            ->method('hasAssociation')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->exactly(1))
            ->method('getAssociationTargetClass')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue('Review'));

        $this->metadata->expects($this->exactly(1))
            ->method('isSingleValuedAssociation')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue(false));

        $this->metadata->expects($this->exactly(1))
            ->method('isCollectionValuedAssociation')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue(true));

        $this->objectManager->expects($this->exactly(3))
            ->method('find')
            ->with('Review', $reviewReference)
            ->will($this->returnValue($review));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertCount(3, $object->reviews);
        $this->assertSame($review, $object->reviews[0]);
        $this->assertSame($review, $object->reviews[1]);
        $this->assertSame($review, $object->reviews[2]);
    }
    
    public function testHydrateCanHandleSingleRelatedObject()
    {
        $review = new stdClass();
        $review->reviewer = 'David Windell';
        $review->description = 'Testing hydration of related objects instead of identifiers';

        $data = array(
            'review' => $review,
        );

        $this->metadata->expects($this->exactly(1))
            ->method('getTypeOfField')
            ->with($this->equalTo('review'))
            ->will($this->returnValue('stdClass'));

        $this->metadata->expects($this->exactly(1))
            ->method('getAssociationTargetClass')
            ->with($this->equalTo('review'))
            ->will($this->returnValue('stdClass'));
        
        $this->metadata->expects($this->exactly(1))
            ->method('hasAssociation')
            ->with($this->equalTo('review'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->exactly(1))
            ->method('isSingleValuedAssociation')
            ->with($this->equalTo('review'))
            ->will($this->returnValue(true));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertSame($review, $object->review);
    }
    
    public function testHydrateCanHandleMultipleRelatedObjects()
    {
        $review = new stdClass();
        $review->reviewer = 'David Windell';
        $review->description = 'Testing hydration of related objects instead of identifiers';

        $data = array(
            'reviews' => array(
                $review,
                $review,
                $review,
            ),
        );

        $this->metadata->expects($this->exactly(1))
            ->method('getTypeOfField')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue('stdClass'));

        $this->metadata->expects($this->exactly(1))
            ->method('hasAssociation')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->exactly(1))
            ->method('getAssociationTargetClass')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue('stdClass'));

        $this->metadata->expects($this->exactly(1))
            ->method('isSingleValuedAssociation')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue(false));

        $this->metadata->expects($this->exactly(1))
            ->method('isCollectionValuedAssociation')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue(true));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertCount(3, $object->reviews);
        $this->assertSame($review, $object->reviews[0]);
        $this->assertSame($review, $object->reviews[1]);
        $this->assertSame($review, $object->reviews[2]);
    }
}
