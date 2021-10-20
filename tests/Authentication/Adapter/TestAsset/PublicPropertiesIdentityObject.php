<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Authentication\Adapter\TestAsset;

/**
 * Simple mock object for authentication adapter test with direct property access
 *
 * @link    http://www.doctrine-project.org/
 */
class PublicPropertiesIdentityObject
{
    /** @var string|null */
    public $username;

    /** @var string|null */
    public $password;
}
