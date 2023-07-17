<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Authentication\Adapter\TestAsset;

/**
 * Simple mock object for authentication adapter test
 */
class IdentityObject
{
    protected string|null $username = null;

    protected string|null $password = null;

    public function setPassword(mixed $password): void
    {
        $this->password = (string) $password;
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    public function setUsername(string $username): void
    {
        $this->username = (string) $username;
    }

    public function getUsername(): string|null
    {
        return $this->username;
    }
}
