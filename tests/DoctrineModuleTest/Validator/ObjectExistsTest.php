<?php

namespace DoctrineModuleTest\Validator\Adapter;

use stdClass;
use PHPUnit\Framework\TestCase as BaseTestCase;
use DoctrineModule\Validator\ObjectExists;

/**
 * Tests for the ObjectExists validator
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 *
 * @covers \DoctrineModule\Validator\ObjectExists
 */
class ObjectExistsTest extends BaseTestCase
{
    public function testCanValidateWithSingleField()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue(new stdClass()));

        $validator = new ObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);

        $this->assertTrue($validator->isValid('matchValue'));
        $this->assertTrue($validator->isValid(['matchKey' => 'matchValue']));
    }

    public function testCanValidateWithMultipleFields()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['firstMatchKey' => 'firstMatchValue', 'secondMatchKey' => 'secondMatchValue'])
            ->will($this->returnValue(new stdClass()));

        $validator = new ObjectExists([
            'object_repository' => $repository,
            'fields'            => [
                'firstMatchKey',
                'secondMatchKey',
            ],
        ]);
        $this->assertTrue(
            $validator->isValid([
                'firstMatchKey'  => 'firstMatchValue',
                'secondMatchKey' => 'secondMatchValue',
            ])
        );
        $this->assertTrue($validator->isValid(['firstMatchValue', 'secondMatchValue']));
    }

    public function testCanValidateFalseOnNoResult()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $validator = new ObjectExists([
            'object_repository' => $repository,
            'fields'            => 'field',
        ]);
        $this->assertFalse($validator->isValid('value'));
    }

    public function testWillRefuseMissingRepository()
    {
        $this->expectException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(['fields' => 'field']);
    }

    public function testWillRefuseNonObjectRepository()
    {
        $this->expectException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(['object_repository' => 'invalid', 'fields' => 'field']);
    }

    public function testWillRefuseInvalidRepository()
    {
        $this->expectException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(['object_repository' => new stdClass(), 'fields' => 'field']);
    }

    public function testWillRefuseMissingFields()
    {
        $this->expectException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists([
            'object_repository' => $this->createMock('Doctrine\Common\Persistence\ObjectRepository'),
        ]);
    }

    public function testWillRefuseEmptyFields()
    {
        $this->expectException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists([
            'object_repository' => $this->createMock('Doctrine\Common\Persistence\ObjectRepository'),
            'fields'            => [],
        ]);
    }

    public function testWillRefuseNonStringFields()
    {
        $this->expectException('Zend\Validator\Exception\InvalidArgumentException');
        new ObjectExists([
            'object_repository' => $this->createMock('Doctrine\Common\Persistence\ObjectRepository'),
            'fields'            => [123],
        ]);
    }

    public function testWillNotValidateOnFieldsCountMismatch()
    {
        $this->expectException(
            'Zend\Validator\Exception\RuntimeException'
        );
        $this->expectExceptionMessage(
            'Provided values count is 1, while expected number of fields to be matched is 2'
        );
        $validator = new ObjectExists([
            'object_repository' => $this->createMock('Doctrine\Common\Persistence\ObjectRepository'),
            'fields'            => ['field1', 'field2'],
        ]);
        $validator->isValid(['field1Value']);
    }

    public function testWillNotValidateOnFieldKeysMismatch()
    {
        $this->expectException(
            'Zend\Validator\Exception\RuntimeException'
        );
        $this->expectExceptionMessage(
            'Field "field2" was not provided, but was expected since the configured field lists needs it for validation'
        );

        $validator = new ObjectExists([
            'object_repository' => $this->createMock('Doctrine\Common\Persistence\ObjectRepository'),
            'fields'            => ['field1', 'field2'],
        ]);

        $validator->isValid(['field1' => 'field1Value']);
    }

    public function testErrorMessageIsStringInsteadArray()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $validator  = new ObjectExists([
            'object_repository' => $this->createMock('Doctrine\Common\Persistence\ObjectRepository'),
            'fields'            => 'field',
        ]);

        $this->assertFalse($validator->isValid('value'));

        $messageTemplates = $validator->getMessageTemplates();

        $expectedMessage = str_replace(
            '%value%',
            'value',
            $messageTemplates[ObjectExists::ERROR_NO_OBJECT_FOUND]
        );
        $messages        = $validator->getMessages();
        $receivedMessage = $messages[ObjectExists::ERROR_NO_OBJECT_FOUND];

        $this->assertSame($expectedMessage, $receivedMessage);
    }
}
