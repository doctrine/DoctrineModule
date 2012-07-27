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
use Traversable;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
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
class DoctrineObject implements HydratorInterface
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
     * @var HydratorInterface
     */
    protected $hydrator;

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
    }

    /**
     * @param HydratorInterface $hydrator
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

        foreach($data as $field => &$value) {
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
                    $value = $this->toMany($value, $target);
                }
            }
        }

        return $this->hydrator->hydrate($data, $object);
    }

    /**
     * @param mixed  $valueOrObject
     * @param string $target
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
     * @param mixed $valueOrObject
     * @param string $target
     * @return array
     */
    protected function toMany($valueOrObject, $target)
    {
        if (!is_array($valueOrObject) && !$valueOrObject instanceof Traversable) {
            $valueOrObject = (array) $valueOrObject;
        }

        $values = array();

        foreach($valueOrObject as $value) {
            if ($value instanceof $target) {
                $values[] = $value;
            } else {
                $values[] = $this->find($target, $value);
            }
        }

        return $values;
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