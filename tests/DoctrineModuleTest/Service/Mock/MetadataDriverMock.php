<?php

namespace DoctrineModuleTest\Service\Mock;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ClassMetadata;

class MetadataDriverMock implements MappingDriver
{
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        return;
    }

    public function isTransient($className)
    {
        return false;
    }

    public function getAllClassNames()
    {
        return [];
    }
}
