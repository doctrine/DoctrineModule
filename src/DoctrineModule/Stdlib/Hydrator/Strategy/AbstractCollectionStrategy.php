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

use RuntimeException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.6.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
abstract class AbstractCollectionStrategy implements StrategyInterface
{
    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var object
     */
    protected $object;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var bool
     */
    protected $useInSelect;


    /**
     * Constructor
     *
     * @param ClassMetadata $metadata
     * @param               $object
     * @param string        $collectionName
     * @param bool          $useInSelect
     */
    public function __construct(ClassMetadata $metadata, $object, $collectionName, $useInSelect = false)
    {
        $this->metadata       = $metadata;
        $this->object         = $object;
        $this->collectionName = $collectionName;
        $this->useInSelect    = (bool) $useInSelect;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value)
    {
        if (!$this->useInSelect || is_numeric($value) || $value === null) {
            return $value;
        }

        $identifierValues = $this->metadata->getIdentifierValues($this->object);

        if (count($identifierValues) > 1) {
            throw new RuntimeException(
                'Doctrine hydrator does not support composite identifiers when collections are used in
                 select form elements (because a select value cannot contain more than one value)'
            );
        }

        // Return the first value of the array
        return reset($identifierValues);
    }

    /**
     * Get the collection value from the object. It first tries to get it using the getter, then trying to get
     * if using the public property (if it exists), and then finally using Reflection
     *
     * @return Collection
     */
    protected function getCollectionFromObject()
    {
        $object = $this->object;
        $getter = 'get' . ucfirst($this->collectionName);

        // Getter
        if (method_exists($object, $getter)) {
            return $object->$getter;
        }

        // Public property
        if (isset($object->{$this->collectionName})) {
            return $object->{$this->collectionName};
        }

        // Reflection
        $refl         = $this->metadata->getReflectionClass();
        $reflProperty = $refl->getProperty($this->collectionName);
        $reflProperty->setAccessible(true);

        return $reflProperty->getValue();
    }

    /**
     * This method is used internally by array_udiff to check if two objects are equal, according to their
     * SPL hash. This is needed because the native array_diff only compare strings
     *
     * @param object $a
     * @param object $b
     */
    protected function compareObjects($a, $b)
    {
        return strcmp(spl_object_hash($a), spl_object_hash($b));
    }
}
