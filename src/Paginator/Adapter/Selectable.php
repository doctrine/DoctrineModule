<?php

declare(strict_types=1);

namespace DoctrineModule\Paginator\Adapter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable as DoctrineSelectable;
use Laminas\Paginator\Adapter\AdapterInterface;

use function count;

/**
 * Provides a wrapper around a Selectable object
 *
 * @template TKey of int
 * @template T
 * @template-implements AdapterInterface<TKey, T>
 */
class Selectable implements AdapterInterface
{
    protected Criteria $criteria;

    /**
     * Create a paginator around a Selectable object. You can also provide an optional Criteria object with
     * some predefined filters
     *
     * @param DoctrineSelectable<TKey,T> $selectable
     */
    public function __construct(protected DoctrineSelectable $selectable, Criteria|null $criteria = null)
    {
        $this->criteria = $criteria ? clone $criteria : new Criteria();
    }

    /**
     * {@inheritDoc}
     *
     * @return iterable<TKey,T>
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->criteria->setFirstResult($offset)->setMaxResults($itemCountPerPage);

        return $this->selectable->matching($this->criteria)->toArray();
    }

    public function count(): int
    {
        $criteria = clone $this->criteria;

        $criteria->setFirstResult(null);
        $criteria->setMaxResults(null);

        return count($this->selectable->matching($criteria));
    }
}
