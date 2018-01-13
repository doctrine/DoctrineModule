<?php

namespace DoctrineModule\Options;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Zend\Authentication\Adapter\Exception;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Stdlib\AbstractOptions;

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
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Michaël Gallego <mic.gallego@gmail.com>
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
     * @var ObjectRepository
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
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
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
     * @var \Zend\Authentication\Storage\StorageInterface|string;
     */
    protected $storage = 'DoctrineModule\Authentication\Storage\Session';

    /**
     * @param  string | ObjectManager $objectManager
     * @return Authentication
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param  ObjectRepository $objectRepository
     * @return Authentication
     */
    public function setObjectRepository(ObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
        return $this;
    }

    /**
     * @return ObjectRepository
     */
    public function getObjectRepository()
    {
        if ($this->objectRepository) {
            return $this->objectRepository;
        }

        return $this->objectManager->getRepository($this->identityClass);
    }

    /**
     * @param string $identityClass
     * @return Authentication
     */
    public function setIdentityClass($identityClass)
    {
        $this->identityClass = $identityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityClass()
    {
        return $this->identityClass;
    }

    /**
     * @param  string $identityProperty
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setIdentityProperty($identityProperty)
    {
        if (! is_string($identityProperty) || $identityProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $identityProperty is invalid, %s given', gettype($identityProperty))
            );
        }

        $this->identityProperty = $identityProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityProperty()
    {
        return $this->identityProperty;
    }

    /**
     * @param  string $credentialProperty
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setCredentialProperty($credentialProperty)
    {
        if (! is_string($credentialProperty) || $credentialProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $credentialProperty is invalid, %s given', gettype($credentialProperty))
            );
        }

        $this->credentialProperty = $credentialProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialProperty()
    {
        return $this->credentialProperty;
    }

    /**
     * @param  mixed $credentialCallable
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setCredentialCallable($credentialCallable)
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

    /**
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        if ($this->classMetadata) {
            return $this->classMetadata;
        }

        return $this->objectManager->getClassMetadata($this->identityClass);
    }

    /**
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $classMetadata
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;
    }

    /**
     * @return \Zend\Authentication\Storage\StorageInterface|string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param \Zend\Authentication\Storage\StorageInterface|string $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }
}
