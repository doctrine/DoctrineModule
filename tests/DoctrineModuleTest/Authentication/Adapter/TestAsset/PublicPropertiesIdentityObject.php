<?php

namespace DoctrineModuleTest\Authentication\Adapter\TestAsset;

/**
 * Simple mock object for authentication adapter tests with direct property access
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class PublicPropertiesIdentityObject
{
    /**
     * @var string|null
     */
    public $username;

    /**
     * @var string|null
     */
    public $password;
}
