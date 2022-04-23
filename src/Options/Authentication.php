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
 * @link    http://www.doctrine-project.org/
 */
class Authentication extends AbstractOptions
{
    /**
     * A valid object implementing ObjectManager interface
     *
     * @var string | ObjectManager
     */
    protected $objectManager;

    /**
     * A valid object implementing ObjectRepository interface (or ObjectManager/identityClass)
     *
     * @var ?ObjectRepository
     */
    protected $objectRepository;

    /**
     * Entity's class name
     *
     * @var string
     */
    protected $identityClass;

    /**
     * Property to use for the identity
     *
     * @var string
     */
    protected $identityProperty;

    /**
     * Property to use for the credential
     *
     * @var string
     */
    protected $credentialProperty;

    /**
     * Callable function to check if a credential is valid
     *
     * @var mixed
     */
    protected $credentialCallable;

    /**
     * If an objectManager is not supplied, this metadata will be used
     * by DoctrineModule/Authentication/Storage/ObjectRepository
     *
     * @var ?ClassMetadata
     */
    protected $classMetadata;

    /**
     * When using this options class to create a DoctrineModule/Authentication/Storage/ObjectRepository
     * this is the storage instance that the object key will be stored in.
     *
     * When using this options class to create an AuthenticationService with and
     * the option storeOnlyKeys == false, this is the storage instance that the whole
     * object will be stored in.
     *
     * @var StorageInterface|string;
     */
    protected $storage = 'DoctrineModule\Authentication\Storage\Session';

    /**
     * @param  string | ObjectManager $objectManager
     */
    public function setObjectManager($objectManager): Authentication
    {
        $this->objectManager = $objectManager;

        return $this;
    }

    /**
     * Causes issue with unit test StorageFactoryTest::testCanInstantiateStorageFromServiceLocator
     * when return type is specified
     * : ObjectManager
     *
     * @return mixed
     */
    public function getObjectManager()
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

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function setIdentityProperty(string $identityProperty): Authentication
    {
        if (! is_string($identityProperty) || $identityProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $identityProperty is invalid, %s given', gettype($identityProperty))
            );
        }

        $this->identityProperty = $identityProperty;

        return $this;
    }

    public function getIdentityProperty(): string
    {
        return $this->identityProperty;
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function setCredentialProperty(string $credentialProperty): Authentication
    {
        if (! is_string($credentialProperty) || $credentialProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $credentialProperty is invalid, %s given', gettype($credentialProperty))
            );
        }

        $this->credentialProperty = $credentialProperty;

        return $this;
    }

    public function getCredentialProperty(): string
    {
        return $this->credentialProperty;
    }

    /**
     * @param  mixed $credentialCallable
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setCredentialCallable($credentialCallable): Authentication
    {
        if (! is_callable($credentialCallable)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '"%s" is not a callable',
                    is_string($credentialCallable) ? $credentialCallable : gettype($credentialCallable)
                )
            );
        }

        $this->credentialCallable = $credentialCallable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCredentialCallable()
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

    /**
     * @return StorageInterface|string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param StorageInterface|string $storage
     */
    public function setStorage($storage): void
    {
        $this->storage = $storage;
    }
}
