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
use InvalidArgumentException;
use Traversable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use DoctrineModule\Util\CollectionUtils;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

/**
 * Hydrator based on Doctrine ObjectManager. Hydrates an object using a wrapped hydrator and
 * by retrieving associations by the given identifiers.
 * Please note that non-scalar values passed to the hydrator are considered identifiers too.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class DoctrineObject extends AbstractHydrator
{
    /**
     * Merging strategies constants
     */
    const COLLECTION_MERGING_INTERSECTION_UNION = 0x01;
    const COLLECTION_MERGING_INTERSECTION = 0x03;
    const COLLECTION_MERGING_UNION = 0x02;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var array
     */
    protected $collectionMergingStrategies = array(
        '*' => self::COLLECTION_MERGING_INTERSECTION_UNION
    );

    /**
     * @param ObjectManager     $objectManager
     * @param HydratorInterface $hydrator
     */
    public function __construct(ObjectManager $objectManager, HydratorInterface $hydrator = null)
    {
        $this->objectManager = $objectManager;

        if (null === $hydrator) {
            $hydrator = new ClassMethodsHydrator(false);
        }

        $this->setHydrator($hydrator);

        parent::__construct();
    }

    /**
     * @param  HydratorInterface $hydrator
     * @return DoctrineObject
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * If set to:
     * - "INTERSECTION_UNION": the collection will contain all the elements that already exist, minus
     *   the ones that does not exist in the data from the submitted collection.
     * - "INTERSECTION": the collection will contain all the elements that already exists and that are also
     *   in the data from the submitted collection.
     * - "UNION": the collection will contain all the elements that already exists and the ones that are new
     *   from the submitted collection.
     *
     * The strategy can be set for a specific collection name or to every collection by using the wildcard
     *
     * @param  int    $mergingStrategy
     * @param  string $collectionName
     * @throws InvalidArgumentException
     * @return DoctrineObject
     */
    public function setCollectionMergingStrategy($mergingStrategy, $collectionName = '*')
    {
        $validStrategies = array(
            self::COLLECTION_MERGING_INTERSECTION_UNION,
            self::COLLECTION_MERGING_INTERSECTION,
            self::COLLECTION_MERGING_UNION
        );

        if (!in_array($mergingStrategy, $validStrategies)) {
            throw new InvalidArgumentException(
                'The merging strategy given does not exist'
            );
        }

        $this->collectionMergingStrategies[$collectionName] = $mergingStrategy;

        return $this;
    }

    /**
     * Get the merging strategy for the collection (or the generic one using wildcard)
     *
     * @param  string $collectionName
     * @return null|int
     */
    public function getCollectionMergingStrategy($collectionName = '*')
    {
        if (isset($this->collectionMergingStrategies[$collectionName])) {
            return $this->collectionMergingStrategies[$collectionName];
        }

        return null;
    }

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        return $this->hydrator->extract($object);
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  object $object
     * @throws \Exception
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        $this->metadata = $this->objectManager->getClassMetadata(get_class($object));

        $object = $this->tryConvertArrayToObject($data, $object);

        foreach($data as $field => &$value) {

            $value = $this->hydrateValue($field, $value);

            if ($value === null) {
                continue;
            }

            // @todo DateTime (and other types) conversion should be handled by doctrine itself in future
            if (in_array($this->metadata->getTypeOfField($field), array('datetime', 'time', 'date'))) {
                if (is_int($value)) {
                    $dt = new DateTime();
                    $dt->setTimestamp($value);
                    $value = $dt;
                } elseif (is_string($value)) {
                    $value = new DateTime($value);
                }
            }

            if ($this->metadata->hasAssociation($field)) {
                $target = $this->metadata->getAssociationTargetClass($field);

                if ($this->metadata->isSingleValuedAssociation($field)) {
                    $value = $this->toOne($value, $target);
                } elseif ($this->metadata->isCollectionValuedAssociation($field)) {
                    $collection = $this->toMany($value, $target);
                    $value      = $this->mergeCollection($collection, $field, $object);
                }
            }
        }

        return $this->hydrator->hydrate($data, $object);
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

        return $this->find($target, $valueOrObject);
    }

    /**
     * @param  mixed $valueOrObject
     * @param  string $target
     * @return ArrayCollection
     */
    protected function toMany($valueOrObject, $target)
    {
        if (!is_array($valueOrObject) && !$valueOrObject instanceof Traversable) {
            $valueOrObject = (array) $valueOrObject;
        }

        $values = new ArrayCollection();

        // In order to avoid to make a "find" against an empty value (for instance when a collection contains
        // an empty value. However, please note that as a side-effect, the empty string '' cannot be used as
        // an identifier
        foreach($valueOrObject as $value) {
            if ($value instanceof $target) {
                $values[] = $value;
            } elseif ($value !== null && $value !== '') {
                $values[] = $this->find($target, $value);
            }
        }

        return $values;
    }

    /**
     * Performs a merge of the collection
     *
     * @param Collection $collection
     * @param string     $field
     * @param object     $object
     * @return Collection
     */
    protected function mergeCollection(Collection $collection, $field, $object)
    {
        $propertyRefl = $this->metadata->getReflectionClass()->getProperty($field);
        $propertyRefl->setAccessible(true);

        $previousCollection = $propertyRefl->getValue($object);

        if (($mergingStrategy = $this->getCollectionMergingStrategy($field)) === null) {
            $mergingStrategy = $this->getCollectionMergingStrategy('*');
        }

        if ($mergingStrategy === self::COLLECTION_MERGING_INTERSECTION) {
            return CollectionUtils::intersection($previousCollection, $collection);
        }

        if ($mergingStrategy === self::COLLECTION_MERGING_UNION) {
            return CollectionUtils::union($previousCollection, $collection);
        }

        return CollectionUtils::intersectUnion($previousCollection, $collection);
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
        $identifierNames  = $this->metadata->getIdentifierFieldNames($object);
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

        return $this->find(get_class($object), $identifierValues);
    }

    /**
     * @param  string    $target
     * @param  mixed     $identifiers
     * @return object
     */
    protected function find($target, $identifiers)
    {
        return $this->objectManager->find($target, $identifiers);
    }
}