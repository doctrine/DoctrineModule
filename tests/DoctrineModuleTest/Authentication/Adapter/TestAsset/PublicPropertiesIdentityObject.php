<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Authentication\Adapter\TestAsset;

/**
 * Simple mock object for authentication adapter tests with direct property access
 *
 * @link    http://www.doctrine-project.org/
 */
class PublicPropertiesIdentityObject
{
    public ?string $username = null;

    public ?string $password = null;
}
