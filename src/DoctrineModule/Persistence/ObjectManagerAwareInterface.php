<?php

namespace DoctrineModule\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

interface ObjectManagerAwareInterface
{
    /**
     * Set the object manager
     *
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager);

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager();
}
