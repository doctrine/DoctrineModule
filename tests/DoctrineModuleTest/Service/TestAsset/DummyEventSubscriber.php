<?php

namespace DoctrineModuleTest\Service\TestAsset;

use Doctrine\Common\EventSubscriber;

/**
 * Dummy event subscriber used to test injections
 */
class DummyEventSubscriber implements EventSubscriber
{
    /**
     * Empty callback method
     */
    public function dummy()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'dummy',
        ];
    }
}
