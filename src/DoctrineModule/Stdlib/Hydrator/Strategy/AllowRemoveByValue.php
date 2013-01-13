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

use LogicException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * When this strategy is used for Collections, if the new collection does not contain elements that are present in
 * the original collection, then this strategy remove elements from the original collection. For instance, if the
 * collection initially contains elements A and B, and that the new collection contains elements B and C, then the
 * final collection will contain elements B and C (while element A will be asked to be removed).
 *
 * This strategy is by value, this means it will use the public API (in this case, adder and remover)
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class AllowRemoveByValue extends AbstractCollectionStrategy
{
    /**
     * {@inheritDoc}
     */
    public function hydrate($value)
    {
        // AllowRemove strategy need "adder" and "remover"
        $adder   = 'add' . ucfirst($this->collectionName);
        $remover = 'remove' . ucfirst($this->collectionName);

        if (!method_exists($this->object, $adder) || !method_exists($this->object, $remover)) {
            throw new LogicException(sprintf(
                'AllowRemove strategy for DoctrineModule hydrator requires both %s and %s to be defined in %s
                 entity domain code, but one or both seem to be missing',
                $adder, $remover, get_class($this->object)
            ));
        }

        $collection = $this->getCollectionFromObjectByValue()->toArray();
        $toAdd      = new ArrayCollection(array_udiff($value, $collection, array($this, 'compareObjects')));
        $toRemove   = new ArrayCollection(array_udiff($collection, $value, array($this, 'compareObjects')));

        $this->object->$adder($toAdd);
        $this->object->$remover($toRemove);

        return $collection;
    }
}
