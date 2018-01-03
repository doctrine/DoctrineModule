<?php

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

/**
 * When this strategy is used for Collections, if the new collection does not contain elements that are present in
 * the original collection, then this strategy remove elements from the original collection. For instance, if the
 * collection initially contains elements A and B, and that the new collection contains elements B and C, then the
 * final collection will contain elements B and C (while element A will be asked to be removed).
 *
 * This strategy is by reference, this means it won't use public API to add/remove elements to the collection
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class AllowRemoveByReference extends AbstractCollectionStrategy
{
    /**
     * {@inheritDoc}
     */
    public function hydrate($value)
    {
        $collection      = $this->getCollectionFromObjectByReference();
        $collectionArray = $collection->toArray();

        $toAdd    = array_udiff($value, $collectionArray, [$this, 'compareObjects']);
        $toRemove = array_udiff($collectionArray, $value, [$this, 'compareObjects']);

        foreach ($toAdd as $element) {
            $collection->add($element);
        }

        foreach ($toRemove as $element) {
            $collection->removeElement($element);
        }

        return $collection;
    }
}
