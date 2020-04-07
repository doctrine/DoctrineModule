<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Validator\Adapter;

use DateTime;
use DoctrineModule\Validator\UniqueObject;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Exception\RuntimeException;
use PHPUnit\Framework\TestCase as BaseTestCase;
use stdClass;
use function str_replace;

/**
 * Tests for the UniqueObject validator
 *
 * @link    http://www.doctrine-project.org/
 */
class UniqueObjectTest extends BaseTestCase
{
    public function testCanValidateWithNotAvailableObjectInRepository() : void
    {
        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue(null));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCanValidateIfThereIsTheSameObjectInTheRepository() : void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertTrue($validator->isValid(['matchKey' => 'matchValue', 'id' => 'identifier']));
    }

    public function testCannotValidateIfThereIsAnotherObjectWithTheSameValueInTheRepository() : void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertFalse($validator->isValid(['matchKey' => 'matchValue', 'id' => 'another identifier']));
    }

    public function testCanFetchIdentifierFromContext() : void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $this->assertTrue($validator->isValid('matchValue', ['id' => 'identifier']));
    }

    public function testThrowsAnExceptionOnUsedButMissingContext() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to be an array but is null');

        $match = new stdClass();

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $validator->isValid('matchValue');
    }

    public function testThrowsAnExceptionOnMissingIdentifier() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to contain id');

        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $validator->isValid('matchValue');
    }

    public function testThrowsAnExceptionOnMissingIdentifierInContext() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to contain id');

        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $validator->isValid('matchValue', []);
    }

    public function testThrowsAnExceptionOnMissingObjectManager() : void
    {
        $this->expectException(\Laminas\Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "object_manager" is required and must be an instance of Doctrine\\Persistence\\ObjectManager, nothing given');

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');

        new UniqueObject([
            'object_repository' => $repository,
            'fields'            => 'matchKey',
        ]);
    }

    public function testThrowsAnExceptionOnWrongObjectManager() : void
    {
        $this->expectException(\Laminas\Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "object_manager" is required and must be an instance of Doctrine\\Persistence\\ObjectManager, stdClass given');

        $objectManager = new stdClass();

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');

        new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
    }

    public function testCanValidateWithNotAvailableObjectInRepositoryByDateTimeObject() : void
    {
        $date       = new \DateTime("17 March 2014");
        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date])
            ->will($this->returnValue(null));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'date',
        ]);

        $this->assertTrue($validator->isValid($date));
    }

    public function testCanFetchIdentifierFromObjectContext() : void
    {
        $context     = new stdClass();
        $context->id = 'identifier';

        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->at(0))
            ->method('getIdentifierValues')
            ->with($context)
            ->will($this->returnValue(['id' => 'identifier']));
        $classMetadata
            ->expects($this->at(1))
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);

        $this->assertTrue($validator->isValid('matchValue', $context));
    }

    public function testErrorMessageIsStringInsteadArray() : void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $this->assertFalse(
            $validator->isValid(
                'matchValue',
                ['matchKey' => 'matchValue', 'id' => 'another identifier']
            )
        );
        $messageTemplates = $validator->getMessageTemplates();

        $expectedMessage = str_replace(
            '%value%',
            'matchValue',
            $messageTemplates[UniqueObject::ERROR_OBJECT_NOT_UNIQUE]
        );
        $messages        = $validator->getMessages();
        $receivedMessage = $messages[UniqueObject::ERROR_OBJECT_NOT_UNIQUE];
        $this->assertSame($expectedMessage, $receivedMessage);
    }
}
