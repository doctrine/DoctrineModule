<?php

declare(strict_types=1);

namespace DoctrineModule\Paginator\Adapter;

use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Laminas\Paginator\Adapter\AdapterInterface;
use function array_values;
use function count;

class Collection implements AdapterInterface
{
    /** @var Doctrine\Common\Collections\Collection */
    protected $collection;

    /**
     * @param mixed[] $collection
     */
    public function __construct(DoctrineCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return array_values($this->collection->slice($offset, $itemCountPerPage));
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->collection);
    }
}
