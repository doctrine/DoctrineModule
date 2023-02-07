<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Authentication\Adapter\TestAsset;

/**
 * Simple mock object for authentication adapter test
 */
class IdentityObject
{
    protected ?string $username = null;

    protected ?string $password = null;

    public function setPassword(mixed $password): void
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
