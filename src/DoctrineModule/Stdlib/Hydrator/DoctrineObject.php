<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule\Stdlib\Hydrator;

use DateTime;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * This hydrator has been completely refactored for DoctrineModule 0.7.0. It provides an easy and powerful way
 * of extracting/hydrator objects in Doctrine, by handling most associations types.
 *
 * Note that now a hydrator is bound to a specific entity (while more standard hydrators can be instanciated once
 * and be used with objects of different types). Most of the time, this won't be a problem as in a form we only
 * create one hydrator. This is by design, because this hydrator uses metadata extensively, so it's more efficient
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class DoctrineObject extends AbstractHydrator
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var bool
     */
    protected $byValue = true;


    /**
     * Constructor
     *
     * @param ObjectManager $objectManager The ObjectManager to use
     * @param string        $targetClass   The FQCN of the hydrated/extracted object
     * @param bool          $byValue       If set to true, hydrator will always use entity's public API
     */
    public function __construct(ObjectManager $objectManager, $targetClass, $byValue = true)
    {
        parent::__construct();

        $this->objectManager    = $objectManager;
        $this->metadata         = $objectManager->getClassMetadata($targetClass);
        $this->byValue          = (bool) $byValue;

        $this->prepare();
    }

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        if ($this->byValue) {
            return $this->extractByValue($object);
        }

        return $this->extractByReference($object);
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        if ($this->byValue) {
            return $this->hydrateByValue($data, $object);
        }

        return $this->hydrateByReference($data, $object);
    }

    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException If a strategy added to a collection does not extend AbstractCollectionStrategy
     */
    public function addStrategy($name, StrategyInterface $strategy)
    {
        if ($this->metadata->hasAssociation($name)) {
            if (!$strategy instanceof Strategy\AbstractCollectionStrategy) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Strategies used for collections valued associations must inherit from '
                        . 'Strategy\AbstractCollectionStrategy, %s given',
                        get_class($strategy)
                    )
                );
            }

            $strategy->setCollectionName($name)
                     ->setClassMetadata($this->metadata);
        }

        return parent::addStrategy($name, $strategy);
    }

    /**
     * Prepare the hydrator by adding strategies to every collection valued associations
     *
     * @return void
     */
    protected function prepare()
    {
        $metadata     = $this->metadata;
        $associations = $metadata->getAssociationNames();

        foreach ($associations as $association) {
            // We only need to prepare collection valued associations
            if ($metadata->isCollectionValuedAssociation($association)) {
                if ($this->byValue) {
                    $this->addStrategy($association, new Strategy\AllowRemoveByValue());
                } else {
                    $this->addStrategy($association, new Strategy\AllowRemoveByReference());
                }
            }
        }
    }

    /**
     * Extract values from an object using a by-value logic (this means that it uses the entity
     * API, in this case, getters)
     *
     * @param  object $object
     * @throws RuntimeException
     * @return array
     */
    protected function extractByValue($object)
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        $methods    = get_class_methods($object);

        $data = array();
        foreach ($fieldNames as $fieldName) {
            $getter = 'get' . ucfirst($fieldName);

            // Ignore unknown fields
            if (!in_array($getter, $methods)) {
                continue;
            }

            $data[$fieldName] = $this->extractValue($fieldName, $object->$getter());
        }

        return $data;
    }

    /**
     * Extract values from an object using a by-reference logic (this means that values are
     * directly fetched without using the public API of the entity, in this case, getters)
     *
     * @param  object $object
     * @return array
     */
    protected function extractByReference($object)
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        $refl       = $this->metadata->getReflectionClass();

        $data = array();
        foreach ($fieldNames as $fieldName) {
            $reflProperty = $refl->getProperty($fieldName);
            $reflProperty->setAccessible(true);

            $data[$fieldName] = $this->extractValue($fieldName, $reflProperty->getValue($object));
        }

        return $data;
    }

    /**
     * Hydrate the object using a by-value logic (this means that it uses the entity API, in this
     * case, setters)
     *
     * @param  array  $data
     * @param  object $object
     * @throws RuntimeException
     * @return object
     */
    protected function hydrateByValue(array $data, $object)
    {
        $object   = $this->tryConvertArrayToObject($data, $object);
        $metadata = $this->metadata;

        foreach ($data as $field => $value) {
            $value  = $this->handleTypeConversions($value, $metadata->getTypeOfField($field));
            $setter = 'set' . ucfirst($field);

            if ($metadata->hasAssociation($field)) {
                $target = $metadata->getAssociationTargetClass($field);

                if ($metadata->isSingleValuedAssociation($field)) {
                    if (!method_exists($object, $setter)) {
                        continue;
                    }

                    $value = $this->toOne($target, $this->hydrateValue($field, $value));
                    $object->$setter($value);
                } elseif ($metadata->isCollectionValuedAssociation($field)) {
                    $this->toMany($object, $field, $target, $value);
                }
            } else {
                if (!method_exists($object, $setter)) {
                    continue;
                }

                $object->$setter($value);
            }
        }

        return $object;
    }

    /**
     * Hydrate the object using a by-reference logic (this means that values are modified directly without
     * using the public API, in this case setters, and hence override any logic that could be done in those
     * setters)
     *
     * @param  array  $data
     * @param  object $object
     * @return object
     */
    protected function hydrateByReference(array $data, $object)
    {
        $object   = $this->tryConvertArrayToObject($data, $object);
        $metadata = $this->metadata;
        $refl     = $metadata->getReflectionClass();

        foreach ($data as $field => $value) {
            // Ignore unknown fields
            if (!$refl->hasProperty($field)) {
                continue;
            }

            $value        = $this->handleTypeConversions($value, $metadata->getTypeOfField($field));
            $reflProperty = $refl->getProperty($field);
            $reflProperty->setAccessible(true);

            if ($metadata->hasAssociation($field)) {
                $target = $metadata->getAssociationTargetClass($field);

                if ($metadata->isSingleValuedAssociation($field)) {
                    $value = $this->toOne($target, $this->hydrateValue($field, $value));
                    $reflProperty->setValue($object, $value);
                } elseif ($metadata->isCollectionValuedAssociation($field)) {
                    $this->toMany($object, $field, $target, $value);
                }
            } else {
                $reflProperty->setValue($object, $value);
            }
        }

        return $object;
    }

    /**
     * This function tries, given an array of data, to convert it to an object if the given array contains
     * an identifier for the object. This is useful in a context of updating existing entities, without ugly
     * tricks like setting manually the existing id directly into the entity
     *
     * @param  array  $data   The data that may contain identifiers keys
     * @param  object $object
     * @return object
     */
    protected function tryConvertArrayToObject($data, $object)
    {
        $metadata         = $this->metadata;
        $identifierNames  = $metadata->getIdentifierFieldNames($object);
        $identifierValues = array();

        if (empty($identifierNames)) {
            return $object;
        }

        foreach ($identifierNames as $identifierName) {
            if (!isset($data[$identifierName]) || empty($data[$identifierName])) {
                return $object;
            }

            $identifierValues[$identifierName] = $data[$identifierName];
        }

        return $this->find($identifierValues, $metadata->getName());
    }

    /**
     * Handle ToOne associations
     *
     * @param  string $target
     * @param  mixed  $value
     * @return object
     */
    protected function toOne($target, $value)
    {
        if ($value instanceof $target) {
            return $value;
        }

        return $this->find($value, $target);
    }

    /**
     * Handle ToMany associations. In proper Doctrine design, Collections should not be swapped, so
     * collections are always handled by reference. Internally, every collection is handled using specials
     * strategies that inherit from AbstractCollectionStrategy class, and that add or remove elements but without
     * changing the collection of the object
     *
     * @param  object $object
     * @param  mixed  $collectionName
     * @param  string $target
     * @param  mixed  $values
     * @return void
     */
    protected function toMany($object, $collectionName, $target, $values)
    {
        if (!is_array($values) && !$values instanceof Traversable) {
            $values = (array) $values;
        }

        $collection = array();

        // If the collection contains identifiers, fetch the objects from database
        foreach ($values as $value) {
            if ($value instanceof $target) {
                $collection[] = $value;
            } elseif ($value !== null) {
                $targetObject = $this->find($value, $target);

                if ($targetObject !== null) {
                    $collection[] = $targetObject;
                }
            }
        }

        // Set the object so that the strategy can extract the Collection from it
        $collectionStrategy = $this->getStrategy($collectionName);

        // Even if this check is applied in addStrategy, subclasses may inject invalid strategies
        if ( ! $collectionStrategy instanceof AbstractCollectionStrategy) {
            throw new InvalidArgumentException(
                sprintf(
                    'Strategies used for collections valued associations must inherit from '
                    . 'Strategy\AbstractCollectionStrategy, %s given',
                    get_class($collectionStrategy)
                )
            );
        }

        $collectionStrategy->setObject($object);

        // We could directly call hydrate method from the strategy, but if people want to override
        // hydrateValue function, they can do it and do their own stuff
        $this->hydrateValue($collectionName, $collection);
    }

    /**
     * Handle various type conversions that should be supported natively by Doctrine (like DateTime)
     *
     * @param  mixed  $value
     * @param  string $typeOfField
     * @return DateTime
     */
    protected function handleTypeConversions($value, $typeOfField)
    {
        switch($typeOfField) {
            case 'datetime':
            case 'time':
            case 'date':
                if (is_int($value)) {
                    $dateTime = new DateTime();
                    $dateTime->setTimestamp($value);
                    $value = $dateTime;
                } elseif (is_string($value)) {
                    $value = new DateTime($value);
                }

                break;
            default:
        }

        return $value;
    }

    /**
     * Find an object by its identifiers
     *
     * @param  mixed   $identifiers
     * @param  string  $targetClass
     *
     * @return object|null
     */
    protected function find($identifiers, $targetClass)
    {
        return $this->objectManager->find($targetClass, $identifiers);
    }
}
