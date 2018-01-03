<?php

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

/**
 * When this strategy is used for Collections, if the new collection does not contain elements that are present in
 * the original collection, then this strategy will not remove those elements. At most, it will add new elements. For
 * instance, if the collection initially contains elements A and B, and that the new collection contains elements B
 * and C, then the final collection will contain elements A, B and C.
 *
 * This strategy is by reference, this means it won't use the public API to remove elements
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class DisallowRemoveByReference extends AbstractCollectionStrategy
{
    /**
     * {@inheritDoc}
     */
    public function hydrate($value)
    {
        $collection      = $this->getCollectionFromObjectByReference();
        $collectionArray = $collection->toArray();

        $toAdd = array_udiff($value, $collectionArray, [$this, 'compareObjects']);

        foreach ($toAdd as $element) {
            $collection->add($element);
        }

        return $collection;
    }
}
