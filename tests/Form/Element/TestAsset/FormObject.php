<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Form\Element\TestAsset;

use Stringable;

use function assert;

/**
 * Simple mock object for form element adapter test
 */
class FormObject implements Stringable
{
    protected int|null $id = null;

    public string|null $email = null;

    protected string|null $username = null;

    protected string|null $firstname = null;

    protected string|null $surname = null;

    protected string|null $password = null;

    protected string|null $optgroup = null;

    public function __toString(): string
    {
        assert($this->username !== null);

        return $this->username;
    }

    public function setId(int $id): self
    {
        $this->id = (int) $id;

        return $this;
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setEmail(string $email): self
    {
        $this->email = (string) $email;

        return $this;
    }

    public function getEmail(): string|null
    {
        return $this->email;
    }

    public function setPassword(string $password): self
    {
        $this->password = (string) $password;

        return $this;
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    public function setUsername(string $username): self
    {
        $this->username = (string) $username;

        return $this;
    }

    public function getUsername(): string|null
    {
        return $this->username;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = (string) $firstname;

        return $this;
    }

    public function getFirstname(): string|null
    {
        return $this->firstname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = (string) $surname;

        return $this;
    }

    public function getSurname(): string|null
    {
        return $this->surname;
    }

    public function getName(): string|null
    {
        return isset($this->firstname) && isset($this->surname) ? $this->firstname . ' ' . $this->surname : null;
    }

    public function getOptgroup(): string|null
    {
        return $this->optgroup;
    }

    public function setOptgroup(string|null $optgroup): void
    {
        $this->optgroup = $optgroup;
    }
}
