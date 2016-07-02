<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModuleTest\Validator\Adapter;

use DoctrineModule\Validator\UniqueObject;
use PHPUnit_Framework_TestCase as BaseTestCase;
use stdClass;

/**
 * Tests for the UniqueObject validator
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Oskar Bley <oskar@programming-php.net>
 */
class UniqueObjectTest extends BaseTestCase
{
    public function testCanValidateWithNotAvailableObjectInRepository()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue(null));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCanValidateIfThereIsTheSameObjectInTheRepository()
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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

    public function testCannotValidateIfThereIsAnotherObjectWithTheSameValueInTheRepository()
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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

    public function testCanFetchIdentifierFromContext()
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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

    public function testThrowsAnExceptionOnUsedButMissingContext()
    {
        $this->expectException('\Zend\Validator\Exception\RuntimeException');
        $this->expectExceptionMessage('Expected context to be an array but is null');

        $match = new stdClass();

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue($match));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $validator->isValid('matchValue');
    }

    public function testThrowsAnExceptionOnMissingIdentifier()
    {
        $this->expectException('\Zend\Validator\Exception\RuntimeException');
        $this->expectExceptionMessage('Expected context to contain id');

        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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

    public function testThrowsAnExceptionOnMissingIdentifierInContext()
    {
        $this->expectException('\Zend\Validator\Exception\RuntimeException');
        $this->expectExceptionMessage('Expected context to contain id');

        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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

    public function testThrowsAnExceptionOnMissingObjectManager()
    {
        $this->expectException('\Zend\Validator\Exception\InvalidArgumentException');
        $this->expectExceptionMessage(
            'Option "object_manager" is required and must be an instance of Doctrine\Common\Persistence\ObjectManager,'
            . ' nothing given'
        );

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');

        new UniqueObject([
            'object_repository' => $repository,
            'fields'            => 'matchKey',
        ]);
    }

    public function testThrowsAnExceptionOnWrongObjectManager()
    {
        $this->expectException('\Zend\Validator\Exception\InvalidArgumentException');
        $this->expectExceptionMessage(
            'Option "object_manager" is required and must be an instance of Doctrine\Common\Persistence\ObjectManager,'
            . ' stdClass given'
        );

        $objectManager = new stdClass();

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');

        new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
    }

    public function testCanValidateWithNotAvailableObjectInRepositoryByDateTimeObject()
    {
        $date       = new \DateTime("17 March 2014");
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date])
            ->will($this->returnValue(null));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'date',
        ]);

        $this->assertTrue($validator->isValid($date));
    }

    public function testCanFetchIdentifierFromObjectContext()
    {
        $context     = new stdClass();
        $context->id = 'identifier';

        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
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

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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

    public function testErrorMessageIsStringInsteadArray()
    {
        $match = new stdClass();

        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(['id' => 'identifier']));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
                      ->method('getClassMetadata')
                      ->with('stdClass')
                      ->will($this->returnValue($classMetadata));

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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
        $this->assertTrue($expectedMessage == $receivedMessage);
    }
}