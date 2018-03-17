<?php

namespace DoctrineModuleTest\Validator\Adapter;

use stdClass;
use PHPUnit\Framework\TestCase as BaseTestCase;
use DoctrineModule\Validator\NoObjectExists;

/**
 * Tests for the NoObjectExists tests
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class NoObjectExistsTest extends BaseTestCase
{
    public function testCanValidateWithNoAvailableObjectInRepository()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);

        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCannotValidateWithAvailableObjectInRepository()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(new stdClass()));

        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);

        $this->assertFalse($validator->isValid('matchValue'));
    }

    public function testErrorMessageIsStringInsteadArray()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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
