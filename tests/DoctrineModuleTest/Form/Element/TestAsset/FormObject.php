<?php

namespace DoctrineModuleTest\Form\Element\TestAsset;

/**
 * Simple mock object for form element adapter tests
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class FormObject
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    protected $username;

    /**
     * @var string|null
     */
    protected $firstname;

    /**
     * @var string|null
     */
    protected $surname;

    /**
     * @var string|null
     */
    protected $password;

    /**
     * @var string|null
     */
    protected $optgroup;

    public function __toString()
    {
        return $this->username;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = (string) $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $firstname
     *
     * @return self
     */
    public function setFirstname($firstname)
    {
        $this->firstname = (string) $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $surname
     *
     * @return self
     */
    public function setSurname($surname)
    {
        $this->surname = (string) $surname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return isset($this->firstname) && isset($this->surname) ? $this->firstname . " " . $this->surname : null;
    }

    /**
     * @return null|string
     */
    public function getOptgroup()
    {
        return $this->optgroup;
    }

    /**
     * @param null|string $optgroup
     */
    public function setOptgroup($optgroup)
    {
        $this->optgroup = $optgroup;
    }
}
