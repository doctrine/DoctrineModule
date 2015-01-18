<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\PropertyNamesAware;

interface ClassMetadataPropertyNamesAware extends ClassMetadata, PropertyNamesAware
{
    
}
