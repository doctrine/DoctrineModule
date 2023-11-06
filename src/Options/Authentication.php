<?php

declare(strict_types=1);

namespace DoctrineModule\Options;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Laminas\Authentication\Adapter\Exception;
use Laminas\Authentication\Storage\StorageInterface;
use Laminas\Stdlib\AbstractOptions;

use function gettype;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * This options class can be consumed by five different classes:
 *
 * DoctrineModule\Authentication\Adapter\ObjectRepository
 * DoctrineModule\Service\Authentication\AdapterFactory
 * DoctrineModule\Authentication\Storage\ObjectRepository
 * DoctrineModule\Service\Authentication\ServiceFactory
 * DoctrineModule\Service\Authentication\AuthenticationServiceFactory
 *
 * When using with DoctrineModule\Authentication\Adapter\ObjectRepository the following
 * options are required:
 *
 * $identityProperty
 * $credentialProperty
 *
 * In addition either $objectRepository or $objectManager and $identityClass must be set.
 * If $objectRepository is set, it takes precedence over $objectManager and $identityClass.
 * If $objectManager is used, it must be an instance of ObjectManager.
 *
 * All remains the same using with DoctrineModule\Service\AuthenticationAdapterFactory,
 * however, a string may be passed to $objectManager. This string must be a valid key to
 * retrieve an ObjectManager instance from the ServiceManager.
 *
 * When using with DoctrineModule\Authentication\Service\Object repository the following
 * options are required:
 *
 * Either $objectManager, or $classMetadata and $objectRepository.
 *
 * All remains the same using with DoctrineModule\Service\AuthenticationStorageFactory,
 * however, a string may be passed to $objectManager. This string must be a valid key to
 * retrieve an ObjectManager instance from the ServiceManager.
 *
 * @template-extends AbstractOptions<mixed>
 */
final class Authentication extends AbstractOptions
{
    /**
     * A valid object implementing ObjectManager interface
     */
    protected string|ObjectManager $objectManager;

    /**
     * A valid object implementing ObjectRepository interface (or ObjectManager/identityClass)
     */
    protected ObjectRepository|null $objectRepository = null;

    /**
     * Entity's class name
     *
     * @psalm-var class-string
     */
    protected string $identityClass;

    /**
     * Property to use for the identity
     */
    protected string $identityProperty;

    /**
     * Property to use for the credential
     */
    protected string $credentialProperty;

    /**
     * Callable function to check if a credential is valid
     */
    protected mixed $credentialCallable = null;

    /**
     * If an objectManager is not supplied, this metadata will be used
     * by DoctrineModule/Authentication/Storage/ObjectRepository
     */
    protected ClassMetadata|null $classMetadata = null;

    /**
     * When using this options class to create a DoctrineModule/Authentication/Storage/ObjectRepository
     * this is the storage instance that the object key will be stored in.
     *
     * When using this options class to create an AuthenticationService with and
     * the option storeOnlyKeys == false, this is the storage instance that the whole
     * object will be stored in.
     */
    protected StorageInterface|string $storage = 'DoctrineModule\Authentication\Storage\Session';

    public function setObjectManager(string|ObjectManager $objectManager): Authentication
    {
        $this->objectManager = $objectManager;

        return $this;
    }

    /**
     * Causes issue with unit test StorageFactoryTest::testCanInstantiateStorageFromServiceLocator
     * when return type is specified
     * : ObjectManager
     */
    public function getObjectManager(): mixed
    {
        return $this->objectManager;
    }

    public function setObjectRepository(ObjectRepository $objectRepository): Authentication
    {
        $this->objectRepository = $objectRepository;

        return $this;
    }

    public function getObjectRepository(): ObjectRepository
    {
        if ($this->objectRepository) {
            return $this->objectRepository;
        }

        return $this->objectManager->getRepository($this->identityClass);
    }

    public function setIdentityClass(string $identityClass): Authentication
    {
        $this->identityClass = $identityClass;

        return $this;
    }

    public function getIdentityClass(): string
    {
        return $this->identityClass;
    }

    /** @throws Exception\InvalidArgumentException */
    public function setIdentityProperty(string $identityProperty): Authentication
    {
        if ($identityProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $identityProperty is invalid, %s given', gettype($identityProperty)),
            );
        }

        $this->identityProperty = $identityProperty;

        return $this;
    }

    public function getIdentityProperty(): string
    {
        return $this->identityProperty;
    }

    /** @throws Exception\InvalidArgumentException */
    public function setCredentialProperty(string $credentialProperty): Authentication
    {
        if ($credentialProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $credentialProperty is invalid, %s given', gettype($credentialProperty)),
            );
        }

        $this->credentialProperty = $credentialProperty;

        return $this;
    }

    public function getCredentialProperty(): string
    {
        return $this->credentialProperty;
    }

    /** @throws Exception\InvalidArgumentException */
    public function setCredentialCallable(mixed $credentialCallable): Authentication
    {
        if (! is_callable($credentialCallable)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '"%s" is not a callable',
                    is_string($credentialCallable) ? $credentialCallable : gettype($credentialCallable),
                ),
            );
        }

        $this->credentialCallable = $credentialCallable;

        return $this;
    }

    public function getCredentialCallable(): mixed
    {
        return $this->credentialCallable;
    }

    public function getClassMetadata(): ClassMetadata
    {
        if ($this->classMetadata) {
            return $this->classMetadata;
        }

        return $this->objectManager->getClassMetadata($this->identityClass);
    }

    public function setClassMetadata(ClassMetadata $classMetadata): void
    {
        $this->classMetadata = $classMetadata;
    }

    public function getStorage(): StorageInterface|string
    {
        return $this->storage;
    }

    public function setStorage(StorageInterface|string $storage): void
    {
        $this->storage = $storage;
    }
}
