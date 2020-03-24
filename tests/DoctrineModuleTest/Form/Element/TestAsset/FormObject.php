<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Form\Element\TestAsset;

use function assert;

/**
 * Simple mock object for form element adapter tests
 *
 * @link    http://www.doctrine-project.org/
 */
class FormObject
{
    /** @var int|null */
    protected $id;

    /** @var string|null */
    public $email;

    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $firstname;

    /** @var string|null */
    protected $surname;

    /** @var string|null */
    protected $password;

    /** @var string|null */
    protected $optgroup;

    public function __toString() : string
    {
        assert($this->username !== null);

        return $this->username;
    }

    public function setId(int $id) : self
    {
        $this->id = (int) $id;

        return $this;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setEmail(string $email) : self
    {
        $this->email = (string) $email;

        return $this;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setPassword(string $password) : self
    {
        $this->password = (string) $password;

        return $this;
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setUsername(string $username) : self
    {
        $this->username = (string) $username;

        return $this;
    }

    public function getUsername() : ?string
    {
        return $this->username;
    }

    public function setFirstname(string $firstname) : self
    {
        $this->firstname = (string) $firstname;

        return $this;
    }

    public function getFirstname() : ?string
    {
        return $this->firstname;
    }

    public function setSurname(string $surname) : self
    {
        $this->surname = (string) $surname;

        return $this;
    }

    public function getSurname() : ?string
    {
        return $this->surname;
    }

    public function getName() : ?string
    {
        return isset($this->firstname) && isset($this->surname) ? $this->firstname . ' ' . $this->surname : null;
    }

    public function getOptgroup() : ?string
    {
        return $this->optgroup;
    }

    public function setOptgroup(?string $optgroup) : void
    {
        $this->optgroup = $optgroup;
    }
}
