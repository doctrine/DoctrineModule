<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use stdClass;
use PHPUnit_Framework_TestCase as BaseTestCase;
use Doctrine\Common\Collections\ArrayCollection;
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

        $reflClass = $this->getMock('\ReflectionClass',
            array(),
            array('Doctrine\Common\Collections\ArrayCollection'));

        $reflProperty = $this->getMock('\ReflProperty',
            array('setAccessible', 'getValue')
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

        $this->metadata->expects($this->exactly(1))
            ->method('getReflectionClass')
            ->will($this->returnValue($reflClass));

        $reflClass->expects($this->exactly(1))
            ->method('getProperty')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue($reflProperty));

        $reflProperty->expects($this->exactly(1))
            ->method('getValue')
            ->withAnyParameters()
            ->will($this->returnValue(new ArrayCollection($data['categories'])));

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

        $reflClass = $this->getMock('\ReflectionClass',
            array(),
            array('Doctrine\Common\Collections\ArrayCollection'));

        $reflProperty = $this->getMock('\ReflProperty',
            array('setAccessible', 'getValue')
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

        $this->metadata->expects($this->exactly(1))
            ->method('getReflectionClass')
            ->will($this->returnValue($reflClass));

        $reflClass->expects($this->exactly(1))
            ->method('getProperty')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue($reflProperty));

        $reflProperty->expects($this->exactly(1))
            ->method('getValue')
            ->withAnyParameters()
            ->will($this->returnValue(new ArrayCollection($data['reviews'])));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertCount(3, $object->reviews);

        foreach ($object->reviews as $review) {
            $this->assertSame($review, $review);
        }
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

        $reflClass = $this->getMock('\ReflectionClass',
            array(),
            array('Doctrine\Common\Collections\ArrayCollection'));

        $reflProperty = $this->getMock('\ReflProperty',
            array('setAccessible', 'getValue')
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

        $this->metadata->expects($this->exactly(1))
            ->method('getReflectionClass')
            ->will($this->returnValue($reflClass));

        $reflClass->expects($this->exactly(1))
            ->method('getProperty')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue($reflProperty));

        $reflProperty->expects($this->exactly(1))
            ->method('getValue')
            ->withAnyParameters()
            ->will($this->returnValue(new ArrayCollection($data['reviews'])));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertCount(3, $object->reviews);
        $this->assertSame($review, $object->reviews[0]);
        $this->assertSame($review, $object->reviews[1]);
        $this->assertSame($review, $object->reviews[2]);
    }

    public function testAlwaysRetrieveArrayCollectionForToManyRelationships()
    {
        $reviewReference = new stdClass();
        $reviewReference->uuid = '5678';

        $review = new stdClass();
        $review->reviewer = 'Michaël Gallego';
        $review->description = 'Testing Array Collection';

        $data = array(
            'reviews' => array(
                $reviewReference,
                $reviewReference,
                $reviewReference,
            ),
        );

        $reflClass = $this->getMock('\ReflectionClass',
            array(),
            array('Doctrine\Common\Collections\ArrayCollection'));

        $reflProperty = $this->getMock('\ReflProperty',
            array('setAccessible', 'getValue')
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

        $this->metadata->expects($this->exactly(1))
            ->method('getReflectionClass')
            ->will($this->returnValue($reflClass));

        $reflClass->expects($this->exactly(1))
            ->method('getProperty')
            ->with($this->equalTo('reviews'))
            ->will($this->returnValue($reflProperty));

        $reflProperty->expects($this->exactly(1))
            ->method('getValue')
            ->withAnyParameters()
            ->will($this->returnValue(new ArrayCollection($data['reviews'])));

        $this->objectManager->expects($this->exactly(3))
            ->method('find')
            ->with('Review', $reviewReference)
            ->will($this->returnValue($review));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertCount(3, $object->reviews);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $object->reviews);
    }

    public function testReturnObjectIfArrayContainIdentifierValues()
    {
        $reviewReference = new stdClass();
        $reviewReference->uuid = '5678';

        $reviewWithId = new stdClass();
        $reviewWithId->id = 5;

        $data = array(
            'id' => '5',
            'reviewer' => 'Michaël Gallego'
        );

        $this->metadata->expects($this->exactly(2))
            ->method('getTypeOfField')
            ->withAnyParameters()
            ->will($this->returnValue('string'));

        $this->metadata->expects($this->exactly(2))
            ->method('hasAssociation')
            ->withAnyParameters()
            ->will($this->returnValue(false));

        $this->metadata->expects($this->exactly(1))
            ->method('getIdentifierFieldNames')
            ->with($this->equalTo(new stdClass()))
            ->will($this->returnValue(array('id')));

        $this->objectManager->expects($this->exactly(1))
            ->method('find')
            ->with('stdClass', array('id' => '5'))
            ->will($this->returnValue($reviewWithId));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertEquals('5', $object->id);
        $this->assertEquals('Michaël Gallego', $object->reviewer);
    }

    /**
     * This data set contains the data that is added to an existing collection. The original collection is always
     * the same, that is to say :
     *  'categories' => [0 => 'foo', 1 => 'bar', 2 => 'french']
     *
     * @return array
     */
    public function collectionMergingProvider()
    {
        $first = new stdClass();
        $first->value = 'foo';
        $second = new stdClass();
        $second->value = 'bar';
        $third = new stdClass();
        $third->value = 'french';
        $fourth = new stdClass();
        $fourth->value = 'italian';
        $fifth = new stdClass();
        $fifth->value = 'umbrella';

        return array(
            // ------------- INTERSECTION UNION STRATEGY -------------
            // Same count, but different values
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second, $fourth
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second, $fourth
                    )
                ),

                'strategy' => 'intersectUnion'
            ),

            // Fewer count
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second
                    )
                ),

                'strategy' => 'intersectUnion'
            ),

            // More count (new elements)
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second, $third, $fourth
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second, $third, $fourth
                    )
                ),

                'strategy' => 'intersectUnion'
            ),


            // ------------- INTERSECTION STRATEGY -------------
            // Same count, but different values
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second, $fourth
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second
                    )
                ),

                'strategy' => 'intersect'
            ),

            // Fewer count
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second
                    )
                ),

                'strategy' => 'intersect'
            ),

            // More count (new elements)
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second, $third, $fourth
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second, $third
                    )
                ),

                'strategy' => 'intersect'
            ),


            // ------------- UNION STRATEGY -------------
            // Same count, but different values
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second, $fourth
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second, $third, $fourth
                    )
                ),

                'strategy' => 'union'
            ),

            // Fewer count
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second, $third
                    )
                ),

                'strategy' => 'union'
            ),

            // More count (new elements)
            array(
                // new collection
                array(
                    'categories' => array(
                        $first, $second, $third, $fourth
                    ),
                ),

                // expected merge
                array(
                    'categories' => array(
                        $first, $second, $third, $fourth
                    )
                ),

                'strategy' => 'union'
            ),
        );
    }

    /**
     * @dataProvider collectionMergingProvider
     */
    public function testAutomaticallyMergeCollections(array $data, array $expected, $strategy)
    {
        $reflClass = $this->getMock('\ReflectionClass',
            array(),
            array('Doctrine\Common\Collections\ArrayCollection'));

        $reflProperty = $this->getMock('\ReflProperty',
            array('setAccessible', 'getValue')
        );

        // Set an object with pre-defined values (we have to create stdClass as element so that elements are passed
        // by reference and not as value, so that we can emulate normal behaviour)
        $categories = $data['categories'];
        if (isset($categories[0]) && $categories[0]->value === 'foo') {
            $first = $categories[0];
        } else {
            $first = new stdClass();
            $first->value = 'foo';
        }

        if (isset($categories[1]) && $categories[1]->value === 'bar') {
            $second = $categories[1];
        } else {
            $second = new stdClass();
            $second->value = 'bar';
        }

        if (isset($categories[2]) && $categories[2]->value === 'french') {
            $third = $categories[2];
        } else {
            $third = new stdClass();
            $third->value = 'french';
        }

        $existingObject = new stdClass();
        $existingObject->categories = array(
            $first, $second, $third
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

        $this->metadata->expects($this->exactly(1))
            ->method('getReflectionClass')
            ->will($this->returnValue($reflClass));

        $reflClass->expects($this->exactly(1))
            ->method('getProperty')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue($reflProperty));

        $reflProperty->expects($this->exactly(1))
            ->method('getValue')
            ->withAnyParameters()
            ->will($this->returnValue(new ArrayCollection($existingObject->categories)));

        if ($strategy === 'intersectUnion') {
            $this->hydrator->setCollectionMergingStrategy(DoctrineObjectHydrator::COLLECTION_MERGING_INTERSECT_UNION, '*');
        } elseif ($strategy === 'intersect') {
            $this->hydrator->setCollectionMergingStrategy(DoctrineObjectHydrator::COLLECTION_MERGING_INTERSECT, '*');
        } elseif ($strategy === 'union') {
            $this->hydrator->setCollectionMergingStrategy(DoctrineObjectHydrator::COLLECTION_MERGING_UNION, '*');
        }

        $object = $this->hydrator->hydrate($data, $existingObject);
        $this->assertSame($object->categories, $existingObject->categories);
        $this->assertEquals(count($expected['categories']), count($object->categories));
        $this->assertEquals(array_values($expected['categories']), array_values($object->categories->toArray()));
    }

    public function testAvoidFailingLookupsForEmptyArrayValues()
    {
        $data = array(
            'categories' => array(
                1, 2, ''
            )
        );

        $reflClass = $this->getMock('\ReflectionClass',
            array(),
            array('Doctrine\Common\Collections\ArrayCollection'));

        $reflProperty = $this->getMock('\ReflProperty',
            array('setAccessible', 'getValue')
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

        $this->objectManager->expects($this->exactly(2))
            ->method('find')
            ->will($this->returnValue(new stdClass()));

        $this->metadata->expects($this->exactly(1))
            ->method('getReflectionClass')
            ->will($this->returnValue($reflClass));

        $reflClass->expects($this->exactly(1))
            ->method('getProperty')
            ->with($this->equalTo('categories'))
            ->will($this->returnValue($reflProperty));

        $reflProperty->expects($this->exactly(1))
            ->method('getValue')
            ->withAnyParameters()
            ->will($this->returnValue(new ArrayCollection($data)));

        $object = $this->hydrator->hydrate($data, new stdClass());
        $this->assertEquals(2, count($object->categories));
    }

    public function testHydratingObjectsWithStrategy()
    {
        $data = array(
            'username' => 'foo',
            'password' => 'bar'
        );

        $modifiedData = array(
            'username' => 'MODIFIED',
            'password' => 'bar'
        );

        $this->hydrator->addStrategy('username', new TestAsset\HydratorStrategy());

        $object  = $this->hydrator->hydrate($data, new stdClass());
        $extract = $this->hydrator->extract($object);

        $this->assertEquals($modifiedData, $extract);
    }
}
