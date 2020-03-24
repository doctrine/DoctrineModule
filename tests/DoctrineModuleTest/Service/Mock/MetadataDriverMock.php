<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Mock;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

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
