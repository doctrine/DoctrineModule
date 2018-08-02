<?php

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use LogicException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Inflector\Inflector;

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
        $adder   = 'add' . Inflector::classify($this->collectionName);
        $remover = 'remove' . Inflector::classify($this->collectionName);

        if (! method_exists($this->object, $adder) || ! method_exists($this->object, $remover)) {
            throw new LogicException(
                sprintf(
                    'AllowRemove strategy for DoctrineModule hydrator requires both %s and %s to be defined in %s
                     entity domain code, but one or both seem to be missing',
                    $adder,
                    $remover,
                    get_class($this->object)
                )
            );
        }

        $collection = $this->getCollectionFromObjectByValue();

        if ($collection instanceof Collection) {
            $collection = $collection->toArray();
        }

        $toAdd    = new ArrayCollection(array_udiff($value, $collection, [$this, 'compareObjects']));
        $toRemove = new ArrayCollection(array_udiff($collection, $value, [$this, 'compareObjects']));

        $this->object->$adder($toAdd);
        $this->object->$remover($toRemove);

        return $collection;
    }
}
