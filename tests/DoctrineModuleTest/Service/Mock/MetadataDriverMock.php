<?php

namespace DoctrineModuleTest\Service\Mock;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

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
        return array();
    }
}
