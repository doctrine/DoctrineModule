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
use DoctrineModule\Stdlib\Hydrator\Strategy\DoctrineFieldStrategy;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * @todo docs
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.8.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ByReferenceHydrator implements HydratorInterface
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
     * @var StrategiesContainer
     */
    protected $strategiesContainer;

    public function __construct(ObjectManager $objectManager, StrategiesContainer $strategiesContainer)
    {
        $this->objectManager       = $objectManager;
        $this->strategiesContainer = $strategiesContainer;
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

        $fieldNames = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        $refl       = $this->metadata->getReflectionClass();

        $data = array();
        foreach ($fieldNames as $fieldName) {
            $reflProperty = $refl->getProperty($fieldName);
            $reflProperty->setAccessible(true);

            $data[$fieldName] = $this->extractValue($fieldName, $reflProperty->getValue($object), $object);
        }

        return $data;
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

        $object   = $this->tryConvertArrayToObject($data, $object);
        $metadata = $this->metadata;
        $refl     = $metadata->getReflectionClass();

        foreach ($data as $field => $value) {
            // Ignore unknown fields
            if (!$refl->hasProperty($field)) {
                continue;
            }

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
     * Prepare the hydrator by adding strategies to every collection valued associations
     *
     * @param  object $object
     *
     * @return void
     */
    protected function prepare($object)
    {
        $this->metadata = $this->objectManager->getClassMetadata(get_class($object));

        $this->strategiesContainer->prepare($object);
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
                if (!$this->strategiesContainer->hasStrategy($association)) {
                    $this->strategiesContainer->addStrategy($association, new Strategy\AllowRemoveByReference());
                }

                $strategy = $this->strategiesContainer->getStrategy($association);

                if (!$strategy instanceof Strategy\AbstractCollectionStrategy) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Strategies used for collections valued associations must inherit from '
                                . 'Strategy\AbstractCollectionStrategy, %s given',
                            get_class($strategy)
                        )
                    );
                }

                $strategy->setCollectionName($association)->setClassMetadata($this->metadata);
            }
        }
    }

    /**
     * This function tries, given an array of data, to convert it to an object if the given array contains
     * an identifier for the object. This is useful in a context of updating existing entities, without ugly
     * tricks like setting manually the existing id directly into the entity
     *
     * @param  array  $data   The data that may contain identifiers keys
     * @param  object $object
     * @return object
     *
     * @todo should probably be removed, as we don't care about what instance of the object is being hydrated
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
            $object = $this->find($identifiers, $target) ?: new $target;
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
        $collectionStrategy = $this->strategiesContainer->getStrategy($collectionName);
        $collectionStrategy->setObject($object);

        // We could directly call hydrate method from the strategy, but if people want to override
        // hydrateValue function, they can do it and do their own stuff
        $this->hydrateValue($collectionName, $collection, $values);
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

    private function extractValue($name, $value, $object = null)
    {
        if ($this->strategiesContainer->hasStrategy($name)) {
            $strategy = $this->strategiesContainer->getStrategy($name);
            $value = $strategy->extract($value, $object);
        }
        return $value;
    }

    private function hydrateValue($name, $value, $data = null)
    {
        if ($this->strategiesContainer->hasStrategy($name)) {
            $strategy = $this->strategiesContainer->getStrategy($name);
            $value = $strategy->hydrate($value, $data);
        }
        return $value;
    }
}
