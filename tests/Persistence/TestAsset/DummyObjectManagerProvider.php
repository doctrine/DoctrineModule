<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Persistence\TestAsset;

use DoctrineModule\Persistence\ProvidesObjectManager;

class DummyObjectManagerProvider
{
    use ProvidesObjectManager;
}
