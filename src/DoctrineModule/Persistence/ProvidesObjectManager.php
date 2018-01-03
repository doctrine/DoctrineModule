<?php

namespace DoctrineModule\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Trait to provide object manager to a form (only works from PHP 5.4)
 */
trait ProvidesObjectManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set the object manager
     *
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
