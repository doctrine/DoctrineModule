<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Authentication\Adapter\TestAsset;

/**
 * Simple mock object for authentication adapter test
 *
 * @link    http://www.doctrine-project.org/
 */
class IdentityObject
{
    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $password;

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = (string) $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setUsername(string $username): void
    {
        $this->username = (string) $username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
