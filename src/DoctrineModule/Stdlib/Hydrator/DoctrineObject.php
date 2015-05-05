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
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\Filter\FilterProviderInterface;

/**
 * This hydrator has been completely refactored for DoctrineModule 0.7.0. It provides an easy and powerful way
 * of extracting/hydrator objects in Doctrine, by handling most associations types.
 *
 * Starting from DoctrineModule 0.8.0, the hydrator can be used multiple times with different objects
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
     * @param bool          $byValue       If set to true, hydrator will always use entity's public API
     */
    public function __construct(ObjectManager $objectManager, $byValue = true)
    {
        parent::__construct();

        $this->objectManager = $objectManager;
        $this->byValue       = (bool) $byValue;
    }

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        $this->prepare($object);

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
        $this->prepare($object);

        if ($this->byValue) {
            return $this->hydrateByValue($data, $object);
        }

        return $this->hydrateByReference($data, $object);
    }

    /**
     * Prepare the hydrator by adding strategies to every collection valued associations
     *
     * @param  object $object
     * @return void
     */
    protected function prepare($object)
    {
        $this->metadata = $this->objectManager->getClassMetadata(get_class($object));
        $this->prepareStrategies();
    }

    /**
     * Prepare strategies before the hydrator is used
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function prepareStrategies()
    {
        $associations = $this->metadata->getAssociationNames();

        foreach ($associations as $association) {
            if ($this->metadata->isCollectionValuedAssociation($association)) {
                // Add a strategy if the association has none set by user
                if (!$this->hasStrategy($association)) {
                    if ($this->byValue) {
                        $this->addStrategy($association, new Strategy\AllowRemoveByValue());
                    } else {
                        $this->addStrategy($association, new Strategy\AllowRemoveByReference());
                    }
                }

                $strategy = $this->getStrategy($association);

                if (!$strategy instanceof Strategy\AbstractCollectionStrategy) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Strategies used for collections valued associations must inherit from '
                            . 'Strategy\AbstractCollectionStrategy, %s given',
                            get_class($strategy)
                        )
                    );
                }

                $strategy->setCollectionName($association)
                         ->setClassMetadata($this->metadata);
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
        $filter     = $object instanceof FilterProviderInterface
            ? $object->getFilter()
            : $this->filterComposite;

        $data = array();
        foreach ($fieldNames as $fieldName) {
            if ($filter && !$filter->filter($fieldName)) {
                continue;
            }
            $getter = 'get' . ucfirst($fieldName);
            $isser  = 'is' . ucfirst($fieldName);

            $dataFieldName = $this->computeExtractFieldName($fieldName);
            if (in_array($getter, $methods)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$getter(), $object);
            } elseif (in_array($isser, $methods)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$isser(), $object);
            } elseif (substr($fieldName, 0, 2) === 'is'
                && ctype_upper(substr($fieldName, 2, 1))
                && in_array($fieldName, $methods)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$fieldName(), $object);
            }

            // Unknown fields are ignored
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
        $filter     = $object instanceof FilterProviderInterface
            ? $object->getFilter()
            : $this->filterComposite;

        $data = array();
        foreach ($fieldNames as $fieldName) {
            if ($filter && !$filter->filter($fieldName)) {
                continue;
            }
            $reflProperty = $refl->getProperty($fieldName);
            $reflProperty->setAccessible(true);

            $dataFieldName        = $this->computeExtractFieldName($fieldName);
            $data[$dataFieldName] = $this->extractValue($fieldName, $reflProperty->getValue($object), $object);
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
        $tryObject = $this->tryConvertArrayToObject($data, $object);
        $metadata  = $this->metadata;

        if (is_object($tryObject)) {
            $object = $tryObject;
        }

        foreach ($data as $field => $value) {
            $field  = $this->computeHydrateFieldName($field);
            $value  = $this->handleTypeConversions($value, $metadata->getTypeOfField($field));
            $setter = 'set' . ucfirst($field);

            if ($metadata->hasAssociation($field)) {
                $target = $metadata->getAssociationTargetClass($field);

                if ($metadata->isSingleValuedAssociation($field)) {
                    if (! method_exists($object, $setter)) {
                        continue;
                    }

                    $value = $this->toOne($target, $this->hydrateValue($field, $value, $data));

                    if (null === $value
                        && !current($metadata->getReflectionClass()->getMethod($setter)->getParameters())->allowsNull()
                    ) {
                        continue;
                    }

                    $object->$setter($value);
                } elseif ($metadata->isCollectionValuedAssociation($field)) {
                    $this->toMany($object, $field, $target, $value);
                }
            } else {
                if (! method_exists($object, $setter)) {
                    continue;
                }

                $object->$setter($this->hydrateValue($field, $value, $data));
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
        $tryObject = $this->tryConvertArrayToObject($data, $object);
        $metadata  = $this->metadata;
        $refl      = $metadata->getReflectionClass();

        if (is_object($tryObject)) {
            $object = $tryObject;
        }

        foreach ($data as $field => $value) {
            $field = $this->computeHydrateFieldName($field);
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
                    $value = $this->toOne($target, $this->hydrateValue($field, $value, $data));
                    $reflProperty->setValue($object, $value);
                } elseif ($metadata->isCollectionValuedAssociation($field)) {
                    $this->toMany($object, $field, $target, $value);
                }
            } else {
                $reflProperty->setValue($object, $this->hydrateValue($field, $value, $data));
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
            if (!isset($data[$identifierName])) {
                return $object;
            }

            $identifierValues[$identifierName] = $data[$identifierName];
        }

        return $this->find($identifierValues, $metadata->getName());
    }

    /**
     * Handle ToOne associations
     *
     * When $value is an array but is not the $target's identifiers, $value is
     * most likely an array of fieldset data. The identifiers will be determined
     * and a target instance will be initialized and then hydrated. The hydrated
     * target will be returned.
     *
     * @param  string $target
     * @param  mixed  $value
     * @return object
     */
    protected function toOne($target, $value)
    {
        $metadata = $this->objectManager->getClassMetadata($target);

        if (is_array($value) && array_keys($value) != $metadata->getIdentifier()) {
            // $value is most likely an array of fieldset data
            $identifiers = array_intersect_key(
                $value,
                array_flip($metadata->getIdentifier())
            );
            $object      = $this->find($identifiers, $target) ?: new $target;
            return $this->hydrate($value, $object);
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
     *
     * @throws \InvalidArgumentException
     *
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
            $collection[] = $this->find($value, $target);
        }

        $collection = array_filter(
            $collection,
            function ($item) {
                return null !== $item;
            }
        );

        // Set the object so that the strategy can extract the Collection from it

        /** @var \DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy $collectionStrategy */
        $collectionStrategy = $this->getStrategy($collectionName);
        $collectionStrategy->setObject($object);

        // We could directly call hydrate method from the strategy, but if people want to override
        // hydrateValue function, they can do it and do their own stuff
        $this->hydrateValue($collectionName, $collection, $values);
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
            case 'datetimetz':
            case 'datetime':
            case 'time':
            case 'date':
                if ('' === $value) {
                    return null;
                }

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
     * Find an object by a given target class and identifier
     *
     * @param  mixed   $identifiers
     * @param  string  $targetClass
     *
     * @return object|null
     */
    protected function find($identifiers, $targetClass)
    {
        if ($identifiers instanceof $targetClass) {
            return $identifiers;
        }

        if ($this->isNullIdentifier($identifiers)) {
            return null;
        }

        return $this->objectManager->find($targetClass, $identifiers);
    }

    /**
     * Verifies if a provided identifier is to be considered null
     *
     * @param  mixed $identifier
     *
     * @return bool
     */
    private function isNullIdentifier($identifier)
    {
        if (null === $identifier) {
            return true;
        }

        if ($identifier instanceof Traversable || is_array($identifier)) {
            $nonNullIdentifiers = array_filter(
                ArrayUtils::iteratorToArray($identifier),
                function ($value) {
                    return null !== $value;
                }
            );

            return empty($nonNullIdentifiers);
        }

        return false;
    }

    /**
     * Applies the naming strategy if there is one set
     *
     * @param string $field
     *
     * @return string
     */
    protected function computeHydrateFieldName($field)
    {
        if ($this->hasNamingStrategy()) {
            $field = $this->getNamingStrategy()->hydrate($field);
        }
        return $field;
    }

    /**
     * Applies the naming strategy if there is one set
     *
     * @param string $field
     *
     * @return string
     */
    protected function computeExtractFieldName($field)
    {
        if ($this->hasNamingStrategy()) {
            $field = $this->getNamingStrategy()->extract($field);
        }
        return $field;
    }
}
