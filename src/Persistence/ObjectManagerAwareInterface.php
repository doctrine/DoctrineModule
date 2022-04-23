<?php

declare(strict_types=1);

namespace DoctrineModule\Persistence;

use Doctrine\Persistence\ObjectManager;

// phpcs:disable SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming
interface ObjectManagerAwareInterface
{
// phpcs:enable SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming

    /**
     * Set the object manager
     */
    public function setObjectManager(ObjectManager $objectManager): void;

    /**
     * Get the object manager
     */
    public function getObjectManager(): ObjectManager;
}
