<?php

declare(strict_types=1);

namespace DoctrineModule\Persistence;

use Doctrine\Persistence\ObjectManager;

interface ObjectManagerAwareInterface
{
    /**
     * Set the object manager
     */
    public function setObjectManager(ObjectManager $objectManager): void;

    /**
     * Get the object manager
     */
    public function getObjectManager(): ObjectManager;
}
