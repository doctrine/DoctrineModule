<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Persistence;

use Doctrine\Persistence\ObjectManager;
use DoctrineModuleTest\Persistence\TestAsset\DummyObjectManagerProvider;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Test for {@see \DoctrineModule\Persistence\ProvidesObjectManager}
 */
class ProvidesObjectManagerTest extends BaseTestCase
{
    public function testSetAndGetObjectManager(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $dummy         = new DummyObjectManagerProvider();
        $dummy->setObjectManager($objectManager);
        $retrieved = $dummy->getObjectManager();

        $this->assertSame($objectManager, $retrieved);
    }
}
