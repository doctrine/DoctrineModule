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

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;

/**
 * Provides a base implementation for extraction strategies which rely
 * on Doctrine's metadata to process entities.
 * 
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Liam O'Boyle <liam@ontheroad.net.nz>
 */
abstract class AbstractExtractObjectStrategy extends AbstractCollectionStrategy
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * All object strategies require an object manager.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Process any object values; no object values are untouched.
     *
     * @return mixed
     */
    public function extract($value)
    {
        if (!is_object($value)) {
            return $value;
        }

        if ($value instanceof Collection) {
            return $this->extractCollection($value);
        } else {
            return $this->extractObject($value);
        }
    }

    /**
     * Process a Collection value.
     *
     * @param Collection $collection
     *
     * @return array
     */
    abstract protected function extractCollection(Collection $collection);

    /**
     * Process an single object value.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    abstract protected function extractObject($object);

    /**
     * Get the identifier for the provided object.
     *
     * Children must implement this to choose between getting values by
     * reference or by value.  Concrete implementations for each strategy
     * are provided in {@link getIdentifierByValue()} and
     * {@link getIdentifierByReference()}.
     *
     * @param mixed         $object
     * @param ClassMetadata $identifiers
     *
     * @return mixed
     */
    abstract protected function getIdentifier($object, $metadata);

    /**
     * Get the metadata for an object.
     *
     * @param [ object | string ] $object The object to get metadata for, or
     *                                    the class name.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected function getMetadata($object)
    {
        return $this->objectManager->getClassMetadata(
            is_object($object) ? get_class($object) : $object
        );
    }

    /**
     * No action is taken on hydration.
     *
     * @return mixed
     */
    public function hydrate($value)
    {
        return $value;
    }

    /**
     * Get the ID from an object by value.
     *
     * @param mixed         $object
     * @param ClassMetadata $identifiers
     *
     * @return mixed
     */
    protected function getIdentifierByValue($object, $metadata)
    {
        $values      = array();
        $identifiers = $metadata->getIdentifier();

        foreach ($identifiers as $fieldName) {
            $getter   = 'get' . ucfirst($fieldName);
            $values[] = $object->$getter();
        }

        return count($values) == 1
            ? current($values)
            : $values;
    }

    /**
     * Get the ID from an object by reference.
     *
     * @param mixed         $object
     * @param ClassMetadata $identifiers
     *
     * @return mixed
     */
    protected function getIdentifierByReference($object, $metadata)
    {
        $values      = array();
        $refl        = new \ReflectionClass($object);
        $identifiers = $metadata->getIdentifier();

        foreach ($identifiers as $fieldName) {
            $reflProperty = $refl->getProperty($fieldName);
            $reflProperty->setAccessible(true);

            $values[] = $reflProperty->getValue($object);
        }

        return count($values) == 1
            ? current($values)
            : $values;
    }
}
