<?php

declare(strict_types=1);

namespace DoctrineModule\Paginator\Adapter;

use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Laminas\Paginator\Adapter\AdapterInterface;

use function array_values;
use function count;

/**
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
class Collection implements AdapterInterface
{
    /** @var DoctrineCollection<TKey,T> */
    protected DoctrineCollection $collection;

    /**
     * @param DoctrineCollection<TKey,T> $collection
     */
    public function __construct(DoctrineCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritDoc}
     *
     * @return list<T>
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return array_values($this->collection->slice($offset, $itemCountPerPage));
    }

    public function count(): int
    {
        return count($this->collection);
    }
}
