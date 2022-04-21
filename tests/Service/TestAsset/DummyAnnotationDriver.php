<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\TestAsset;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver as AnnotationDriverPersistence;

class DummyAnnotationDriver extends AnnotationDriverPersistence
{
    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }
}
