<?php

declare(strict_types=1);

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
    public function dummy() : void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return ['dummy'];
    }
}
