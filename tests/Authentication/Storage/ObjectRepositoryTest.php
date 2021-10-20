<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Authentication\Storage;

use DoctrineModule\Authentication\Storage\ObjectRepository as ObjectRepositoryStorage;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject;
use Laminas\Authentication\Storage\NonPersistent as NonPersistentStorage;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Tests for the ObjectRepository based authentication adapter
 *
 * @link    http://www.doctrine-project.org/
 */
class ObjectRepositoryTest extends BaseTestCase
{
    public function testCanRetrieveEntityFromObjectRepositoryStorage(): void
    {
        // Identifier is considered to be username here
        $entity = new IdentityObject();
        $entity->setUsername('a username');
        $entity->setPassword('a password');

        $objectRepository = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $objectRepository->expects($this->exactly(1))
                         ->method('find')
                         ->with($this->equalTo('a username'))
                         ->will($this->returnValue($entity));

        $metadata = $this->createMock('Doctrine\Persistence\Mapping\ClassMetadata');
        $metadata->expects($this->exactly(1))
                 ->method('getIdentifierValues')
                 ->with($this->equalTo($entity))
                 ->will($this->returnValue($entity->getUsername()));

        $storage = new ObjectRepositoryStorage([
            'objectRepository' => $objectRepository,
            'classMetadata' => $metadata,
            'storage' => new NonPersistentStorage(),
        ]);

        $storage->write($entity);
        $this->assertFalse($storage->isEmpty());

        $result = $storage->read();
        $this->assertEquals($entity, $result);

        $key = $storage->readKeyOnly();
        $this->assertEquals('a username', $key);
    }
}
