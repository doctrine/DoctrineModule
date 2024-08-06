<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Validator\Adapter;

use DateTime;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use DoctrineModule\Validator\UniqueObject;
use InvalidArgumentException;
use Laminas\Validator\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase as BaseTestCase;
use stdClass;

use function sprintf;
use function str_replace;

/**
 * Tests for the UniqueObject validator
 */
class UniqueObjectTest extends BaseTestCase
{
    #[Test]
    public function testCanValidateWithNotAvailableObjectInRepository(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue(null));

        $objectManager = $this->createMock(ObjectManager::class);

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertTrue($validator->isValid('matchValue'));
    }

    #[Test]
    public function testCanValidateIfThereIsTheSameObjectInTheRepository(): void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock(ObjectRepository::class);
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

    #[Test]
    public function testCannotValidateIfThereIsAnotherObjectWithTheSameValueInTheRepository(): void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock(ObjectRepository::class);
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

    #[Test]
    public function testCanFetchIdentifierFromContext(): void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock(ObjectRepository::class);
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

    #[Test]
    public function testThrowsAnExceptionOnUsedButMissingContext(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to be an array but is null');

        $match = new stdClass();

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $objectManager = $this->createMock(ObjectManager::class);

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $validator->isValid('matchValue');
    }

    #[Test]
    public function testThrowsAnExceptionOnMissingIdentifier(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to contain id');

        $match = new stdClass();

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock(ObjectRepository::class);
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

    #[Test]
    public function testThrowsAnExceptionOnMissingIdentifierInContext(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to contain id');

        $match = new stdClass();

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock(ObjectRepository::class);
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

    #[Test]
    public function testThrowsAnExceptionOnMissingObjectManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Option "object_manager" is required and must be an instance of %s, nothing given',
            ObjectManager::class,
        ));

        $repository = $this->createMock(ObjectRepository::class);

        new UniqueObject([
            'object_repository' => $repository,
            'fields'            => 'matchKey',
        ]);
    }

    #[Test]
    public function testThrowsAnExceptionOnWrongObjectManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Option "object_manager" is required and must be an instance of %s, stdClass given',
            ObjectManager::class,
        ));

        $objectManager = new stdClass();

        $repository = $this->createMock(ObjectRepository::class);

        new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
    }

    #[Test]
    public function testCanValidateWithNotAvailableObjectInRepositoryByDateTimeObject(): void
    {
        $date       = new DateTime('17 March 2014');
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date])
            ->will($this->returnValue(null));

        $objectManager = $this->createMock(ObjectManager::class);

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'date',
        ]);

        $this->assertTrue($validator->isValid($date));
    }

    #[Test]
    public function testCanFetchIdentifierFromObjectContext(): void
    {
        $context     = new stdClass();
        $context->id = 'identifier';

        $match = new stdClass();

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->atLeastOnce())
            ->method('getIdentifierValues')
            ->willReturnMap([
                [$context, ['id' => 'identifier']],
                [$match, ['id' => 'identifier']],
            ]);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $repository = $this->createMock(ObjectRepository::class);
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

    #[Test]
    public function testErrorMessageIsStringInsteadArray(): void
    {
        $match = new stdClass();

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock(ObjectRepository::class);
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
                ['matchKey' => 'matchValue', 'id' => 'another identifier'],
            ),
        );
        $messageTemplates = $validator->getMessageTemplates();

        $expectedMessage = str_replace(
            '%value%',
            'matchValue',
            $messageTemplates[UniqueObject::ERROR_OBJECT_NOT_UNIQUE],
        );
        $messages        = $validator->getMessages();
        $receivedMessage = $messages[UniqueObject::ERROR_OBJECT_NOT_UNIQUE];
        $this->assertSame($expectedMessage, $receivedMessage);
    }
}
