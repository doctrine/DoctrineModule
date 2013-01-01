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

use InvalidArgumentException;
use Doctrine\Common\Collections\Collection;
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
     * @var object
     */
    protected $object;

    /**
     * @var string
     */
    protected $collectionName;


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
     * Set the object
     *
     * @param  object $object
     * @return AbstractCollectionStrategy
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf(
                'The parameter given to setObject method of %s class is not an object',
                get_called_class()
            ));
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
     * Get the collection value from the object. It first tries to get it using the getter and then trying to get
     * if using the public property (if it exists)
     *
     * @return Collection
     */
    protected function getCollectionFromObject()
    {
        $object = $this->getObject();
        $getter = 'get' . ucfirst($this->collectionName);

        // Getter
        if (method_exists($object, $getter)) {
            return $object->$getter();
        }

        // Public property
        if (isset($object->{$this->collectionName})) {
            return $object->{$this->collectionName};
        }
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

