<?php

namespace DoctrineModuleTest\Util;

use PHPUnit_Framework_TestCase as BaseTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Util\CollectionUtils;

class CollectionUtilsTest extends BaseTestCase
{
    public function testCanIntersectUnionTwoArrays()
    {
        $masterCollection = new ArrayCollection(array(
            'foo',
            'bar'
        ));

        $collection2 = new ArrayCollection(array(
            'foo',
            'baz'
        ));

        $result = CollectionUtils::intersectUnion($masterCollection, $collection2);

        $this->assertSame($result, $masterCollection);

        $resultArray = array_values($result->toArray());
        $this->assertEquals(array(
            'foo', 'baz'
        ), $resultArray);
    }
}
