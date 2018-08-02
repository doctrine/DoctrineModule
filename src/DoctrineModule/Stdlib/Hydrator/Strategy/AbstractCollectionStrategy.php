<?php

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use InvalidArgumentException;
use Zend\Hydrator\Strategy\StrategyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Inflector\Inflector;

/**
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
abstract class AbstractCollectionStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var object
     */
    protected $object;


    /**
     * Set the name of the collection
     *
     * @param  string $collectionName
     * @return AbstractCollectionStrategy
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = (string) $collectionName;
        return $this;
    }

    /**
     * Get the name of the collection
     *
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * Set the class metadata
     *
     * @param  ClassMetadata $classMetadata
     * @return AbstractCollectionStrategy
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->metadata = $classMetadata;
        return $this;
    }

    /**
     * Get the class metadata
     *
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set the object
     *
     * @param  object $object
     *
     * @throws \InvalidArgumentException
     *
     * @return AbstractCollectionStrategy
     */
    public function setObject($object)
    {
        if (! is_object($object)) {
            throw new InvalidArgumentException(
                sprintf('The parameter given to setObject method of %s class is not an object', get_called_class())
            );
        }

        $this->object = $object;
        return $this;
    }

    /**
     * Get the object
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value)
    {
        return $value;
    }

    /**
     * Return the collection by value (using the public API)
     *
     * @throws \InvalidArgumentException
     *
     * @return Collection
     */
    protected function getCollectionFromObjectByValue()
    {
        $object = $this->getObject();
        $getter = 'get' . Inflector::classify($this->getCollectionName());

        if (! method_exists($object, $getter)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The getter %s to access collection %s in object %s does not exist',
                    $getter,
                    $this->getCollectionName(),
                    get_class($object)
                )
            );
        }

        return $object->$getter();
    }

    /**
     * Return the collection by reference (not using the public API)
     *
     * @return Collection
     */
    protected function getCollectionFromObjectByReference()
    {
        $object       = $this->getObject();
        $refl         = $this->getClassMetadata()->getReflectionClass();
        $reflProperty = $refl->getProperty($this->getCollectionName());

        $reflProperty->setAccessible(true);

        return $reflProperty->getValue($object);
    }


    /**
     * This method is used internally by array_udiff to check if two objects are equal, according to their
     * SPL hash. This is needed because the native array_diff only compare strings
     *
     * @param object $a
     * @param object $b
     *
     * @return int
     */
    protected function compareObjects($a, $b)
    {
        return strcmp(spl_object_hash($a), spl_object_hash($b));
    }
}
