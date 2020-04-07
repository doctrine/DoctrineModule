<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Mock;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ClassMetadata;

class MetadataDriverMock implements MappingDriver
{
    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata) : void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient($className) : bool
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getAllClassNames() : array
    {
        return [];
    }
}
