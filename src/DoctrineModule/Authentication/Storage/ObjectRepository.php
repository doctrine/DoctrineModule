<?php

namespace DoctrineModule\Authentication\Storage;

use DoctrineModule\Options\Authentication as AuthenticationOptions;
use Zend\Authentication\Storage\StorageInterface;

/**
 * This class implements StorageInterface and allow to save the result of an authentication against an object repository
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ObjectRepository implements StorageInterface
{

    /**
     *
     * @var \DoctrineModule\Options\Authentication
     */
    protected $options;

    /**
     * @param  array | \DoctrineModule\Options\Authentication $options
     * @return ObjectRepository
     */
    public function setOptions($options)
    {
        if (! $options instanceof AuthenticationOptions) {
            $options = new AuthenticationOptions($options);
        }

        $this->options = $options;
        return $this;
    }

    /**
     * Constructor
     *
     * @param array | \DoctrineModule\Options\Authentication $options
     */
    public function __construct($options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->options->getStorage()->isEmpty();
    }

    /**
     * This function assumes that the storage only contains identifier values (which is the case if
     * the ObjectRepository authentication adapter is used).
     *
     * @return null|object
     */
    public function read()
    {
        if (($identity = $this->options->getStorage()->read())) {
            return $this->options->getObjectRepository()->find($identity);
        }

        return null;
    }

    /**
     * Will return the key of the identity. If only the key is needed, this avoids an
     * unnecessary db call
     *
     * @return mixed
     */
    public function readKeyOnly()
    {
        return $identity = $this->options->getStorage()->read();
    }

    /**
     * @param  object $identity
     * @return void
     */
    public function write($identity)
    {
        $metadataInfo     = $this->options->getClassMetadata();
        $identifierValues = $metadataInfo->getIdentifierValues($identity);

        $this->options->getStorage()->write($identifierValues);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->options->getStorage()->clear();
    }
}
