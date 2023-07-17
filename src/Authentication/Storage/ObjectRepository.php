<?php

declare(strict_types=1);

namespace DoctrineModule\Authentication\Storage;

use DoctrineModule\Options\Authentication as AuthenticationOptions;
use Laminas\Authentication\Storage\StorageInterface;

/**
 * This class implements StorageInterface and allow to save the result of an authentication against an object repository
 */
class ObjectRepository implements StorageInterface
{
    protected AuthenticationOptions $options;

    /** @param mixed[]|AuthenticationOptions $options */
    public function setOptions(array|AuthenticationOptions $options): ObjectRepository
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
     * @param mixed[]|AuthenticationOptions $options
     */
    public function __construct(array|AuthenticationOptions $options = [])
    {
        $this->setOptions($options);
    }

    public function isEmpty(): bool
    {
        return $this->options->getStorage()->isEmpty();
    }

    /**
     * This function assumes that the storage only contains identifier values (which is the case if
     * the ObjectRepository authentication adapter is used).
     */
    public function read(): object|null
    {
        $identity = $this->options->getStorage()->read();
        if ($identity) {
            return $this->options->getObjectRepository()->find($identity);
        }

        return null;
    }

    /**
     * Will return the key of the identity. If only the key is needed, this avoids an
     * unnecessary db call
     */
    public function readKeyOnly(): mixed
    {
        return $identity = $this->options->getStorage()->read();
    }

    public function write(mixed $contents): void
    {
        $metadataInfo     = $this->options->getClassMetadata();
        $identifierValues = $metadataInfo->getIdentifierValues($contents);

        $this->options->getStorage()->write($identifierValues);
    }

    public function clear(): void
    {
        $this->options->getStorage()->clear();
    }
}
