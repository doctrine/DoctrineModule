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
use RuntimeException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\Stdlib\Hydrator\AbstractHydrator;

/**
 * Hydrator based on Doctrine ObjectManager. Hydrates an object using a wrapped hydrator and
 * by retrieving associations by the given identifiers.
 *
 * It was completely refactored given DoctrineModule 0.6.0
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.6.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class DoctrineObject extends AbstractHydrator
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $loadedMetadata = array();

    /**
     * @var bool
     */
    protected $byValue = true;


    /**
     * @param ObjectManager $objectManager
     * @param bool          $byValue
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
        if ($this->byValue === true) {
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
        if ($this->byValue === true) {
            return $this->hydrateByValue($data, $object);
        }

        return $this->hydrateByReference($data, $object);
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
        $metadata   = $this->getMetadataFor(get_class($object));
        $fieldNames = $metadata->getFieldNames();
        $methods    = get_class_methods($object);

        $data = array();
        foreach ($fieldNames as $fieldName) {
            $getter = 'get' . ucfirst($fieldName);

            // Ignore unknown fields
            if (!in_array($getter, $methods)) {
                continue;
            }

            if ($metadata->isSingleValuedAssociation($fieldName)) {
                $data[$fieldName] = $this->extractValue($fieldName, clone $object->$getter());
            } elseif ($metadata->isCollectionValuedAssociation($fieldName)) {
                // We don't clone Collection is this is prohibited
                $data[$fieldName] = $this->extractValue($fieldName, $object->$getter());
            }
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
        $metadata   = $this->getMetadataFor(get_class($object));
        $fieldNames = $metadata->getFieldNames();
        $refl       = $metadata->getReflectionClass();

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
        $metadata = $this->getMetadataFor(get_class($object));

        foreach ($data as $field => $value) {
            if ($value === null) {
                continue;
            }

            $value  = $this->handleTypeConversions($value, $metadata->getTypeOfField($field));
            $setter = 'set' . ucfirst($field);

            if ($metadata->hasAssociation($field)) {
                $target = $metadata->getAssociationTargetClass($field);

                if ($metadata->isSingleValuedAssociation($field)) {
                    $value  = $this->toOne($this->hydrateValue($field, $value), $target);
                    $object->$setter(clone $value);
                } elseif ($metadata->isCollectionValuedAssociation($field)) {
                    // Check for strategy (like if it has a AllowRemove, DisallowRemove...).
                    if (!$this->hasStrategy($field)) {
                        $defaultStrategy = new Strategy\AllowRemove($this->objectManager, $object, $field);
                        $this->addStrategy($field, $defaultStrategy);
                    }

                    // As collection are always handled "by reference", it will directly modify the collection
                    $this->hydrateValue($field, $value);
                }
            } else {
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
        $metadata = $this->getMetadataFor(get_class($object));
        $refl     = $metadata->getReflectionClass();

        foreach ($data as $field => $value) {
            // Ignore unknown fields or null values
            if ($value === null || !$refl->hasProperty($field)) {
                continue;
            }

            $value        = $this->handleTypeConversions($value, $metadata->getTypeOfField($field));
            $reflProperty = $refl->getProperty($field);
            $reflProperty->setAccessible(true);

            if ($metadata->hasAssociation($field)) {
                $target = $metadata->getAssociationTargetClass($field);

                if ($metadata->isSingleValuedAssociation($field)) {
                    $value  = $this->toOne($this->hydrateValue($field, $value), $target);
                    $reflProperty->setValue($object, $value);
                } elseif ($metadata->isCollectionValuedAssociation($field)) {
                    // Check for strategy (like if it has a AllowRemove, DisallowRemove...).
                    if (!$this->hasStrategy($field)) {
                        $defaultStrategy = new Strategy\AllowRemove($this->objectManager, $object, $field);
                        $this->addStrategy($field, $defaultStrategy);
                    }

                    // As collection are always handled "by reference", it will directly modify the collection
                    $this->hydrateValue($field, $value);
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
     * @param  array  $data
     * @param  object $object
     * @return object
     */
    protected function tryConvertArrayToObject($data, $object)
    {
        $objectClassName  = get_class($object);
        $metadata         = $this->getMetadataFor($objectClassName);
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

        return $this->find($objectClassName, $identifierValues);
    }

    /**
     * @param  mixed  $valueOrObject
     * @param  string $target
     * @return object
     */
    protected function toOne($valueOrObject, $target)
    {
        if ($valueOrObject instanceof $target) {
            return $valueOrObject;
        }

        if ($valueOrObject === '') {
            return null;
        }

        return $this->find($target, $valueOrObject);
    }

    /**
     * @param mixed  $valueOrObject
     * @param string $target
     */
    protected function toMany($valueOrObject, $target)
    {

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
     * @param  string  $target
     * @param  mixed   $identifiers
     * @return object
     */
    protected function find($target, $identifiers)
    {
        return $this->objectManager->find($target, $identifiers);
    }

    /**
     * Get the metadata for given class
     *
     * @param  string $className
     * @return ClassMetadata
     */
    private function getMetadataFor($className)
    {
        if (!isset($this->loadedMetadata[$className])) {
            $this->loadedMetadata[$className] = $this->objectManager->getClassMetadata($className);
        }

        return $this->loadedMetadata[$className];
    }
}
