<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Validator\Adapter;

use DoctrineModule\Validator\NoObjectExists;
use PHPUnit\Framework\TestCase as BaseTestCase;
use stdClass;

use function str_replace;

/**
 * Tests for the NoObjectExists test
 */
class NoObjectExistsTest extends BaseTestCase
{
    public function testCanValidateWithNoAvailableObjectInRepository(): void
    {
        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);

        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCannotValidateWithAvailableObjectInRepository(): void
    {
        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(new stdClass()));

        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);

        $this->assertFalse($validator->isValid('matchValue'));
    }

    public function testErrorMessageIsStringInsteadArray(): void
    {
        $repository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(new stdClass()));
        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);

        $this->assertFalse($validator->isValid('matchValue'));

        $messageTemplates = $validator->getMessageTemplates();

        $expectedMessage = str_replace(
            '%value%',
            'matchValue',
            $messageTemplates[NoObjectExists::ERROR_OBJECT_FOUND]
        );
        $messages        = $validator->getMessages();
        $receivedMessage = $messages[NoObjectExists::ERROR_OBJECT_FOUND];

        $this->assertSame($expectedMessage, $receivedMessage);
    }
}
