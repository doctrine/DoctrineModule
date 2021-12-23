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
    protected ?string $username = null;

    protected ?string $password = null;

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
