<?php

namespace DoctrineModuleTest\Stdlib\Hydrator;

use DateTime;
use ReflectionClass;
use PHPUnit\Framework\TestCase as BaseTestCase;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineObjectHydrator;

class DoctrineObjectTypeConversionsTest extends BaseTestCase
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

        $this->metadata      = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

        $this->objectManager->expects($this->any())
                            ->method('getClassMetadata')
                            ->will($this->returnValue($this->metadata));
    }

    /**
     * @param string $genericFieldType
     */
    public function configureObjectManagerForSimpleEntityWithGenericField($genericFieldType)
    {
        $refl = new ReflectionClass('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntityWithGenericField');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntityWithGenericField'));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'genericField']));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->with($this->logicalOr($this->equalTo('id'), $this->equalTo('genericField')))
            ->will(
                $this->returnCallback(

                    /**
                     * @param string $arg
                     */
                    function ($arg) use ($genericFieldType) {
                        if ('id' === $arg) {
                            return 'integer';
                        } elseif ('genericField' === $arg) {
                            return $genericFieldType;
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

    public function testHandleTypeConversionsDatetime()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('datetime');

        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $now->setTimestamp(1522353676);
        $data = ['genericField' => 1522353676];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => $now->format('Y-m-d\TH:i:s\.u')];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => clone $now];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());
    }

    public function testHandleTypeConversionsDatetimetz()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('datetimetz');

        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $now->setTimestamp(1522353676);
        $data = ['genericField' => 1522353676];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => $now->format('Y-m-d\TH:i:s\.u')];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => clone $now];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());
    }

    public function testHandleTypeConversionsTime()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('time');

        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $now->setTimestamp(1522353676);
        $data = ['genericField' => 1522353676];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => $now->format('Y-m-d\TH:i:s\.u')];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => clone $now];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());
    }

    public function testHandleTypeConversionsDate()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('date');

        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $now->setTimestamp(1522353676);
        $data = ['genericField' => 1522353676];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => $now->format('Y-m-d\TH:i:s\.u')];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $now = new DateTime();
        $data = ['genericField' => clone $now];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertInstanceOf('DateTime', $entity->getGenericField());
        $this->assertEquals($now, $entity->getGenericField());
    }

    public function testHandleTypeConversionsInteger()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('integer');

        $entity = new Asset\SimpleEntityWithGenericField();
        $value = 123465;
        $data = ['genericField' => '123465'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_integer($entity->getGenericField()));
        $this->assertEquals($value, $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $value = 123465;
        $data = ['genericField' => '123465'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_integer($entity->getGenericField()));
        $this->assertEquals($value, $entity->getGenericField());
    }

    public function testHandleTypeConversionsSmallint()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('smallint');

        $entity = new Asset\SimpleEntityWithGenericField();
        $value = 123465;
        $data = ['genericField' => '123465'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_integer($entity->getGenericField()));
        $this->assertEquals($value, $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $value = 123465;
        $data = ['genericField' => '123465'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_integer($entity->getGenericField()));
        $this->assertEquals($value, $entity->getGenericField());
    }

    public function testHandleTypeConversionsFloat()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('float');

        $entity = new Asset\SimpleEntityWithGenericField();
        $value = 123.465;
        $data = ['genericField' => '123.465'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_float($entity->getGenericField()));
        $this->assertEquals($value, $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $value = 123.465;
        $data = ['genericField' => '123.465'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_float($entity->getGenericField()));
        $this->assertEquals($value, $entity->getGenericField());
    }

    public function testHandleTypeConversionsBoolean()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('boolean');

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => true];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_bool($entity->getGenericField()));
        $this->assertEquals(true, $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => true];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_bool($entity->getGenericField()));
        $this->assertEquals(true, $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 1];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_bool($entity->getGenericField()));
        $this->assertEquals(true, $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 1];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_bool($entity->getGenericField()));
        $this->assertEquals(true, $entity->getGenericField());
    }

    public function testHandleTypeConversionsString()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('string');

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 12345];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('12345', $entity->getGenericField());
    }

    public function testHandleTypeConversionsText()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('text');

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 12345];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('12345', $entity->getGenericField());
    }

    public function testHandleTypeConversionsBigint()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('bigint');

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 'stringvalue'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('stringvalue', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 12345];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('12345', $entity->getGenericField());
    }

    public function testHandleTypeConversionsDecimal()
    {
        // When using hydration by value, it will use the public API of the entity to set values (setters)
        $this->configureObjectManagerForSimpleEntityWithGenericField('decimal');

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => '123.45'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('123.45', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => '123.45'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('123.45', $entity->getGenericField());


        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => '123.45'];

        $entity = $this->hydratorByValue->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('123.45', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => '123.45'];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('123.45', $entity->getGenericField());

        $entity = new Asset\SimpleEntityWithGenericField();
        $data = ['genericField' => 12345];

        $entity = $this->hydratorByReference->hydrate($data, $entity);

        $this->assertTrue(is_string($entity->getGenericField()));
        $this->assertEquals('12345', $entity->getGenericField());
    }
}
