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
 * @link    http://www.doctrine-project.org/
 */
class Selectable implements AdapterInterface
{
    /** @var DoctrineSelectable */
    protected $selectable;

    /** @var Criteria */
    protected $criteria;

    /**
     * Create a paginator around a Selectable object. You can also provide an optional Criteria object with
     * some predefined filters
     */
    public function __construct(DoctrineSelectable $selectable, ?Criteria $criteria = null)
    {
        $this->selectable = $selectable;
        $this->criteria   = $criteria ? clone $criteria : new Criteria();
    }

    /**
     * {@inheritDoc}
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->criteria->setFirstResult($offset)->setMaxResults($itemCountPerPage);

        return $this->selectable->matching($this->criteria)->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $criteria = clone $this->criteria;

        $criteria->setFirstResult(null);
        $criteria->setMaxResults(null);

        return count($this->selectable->matching($criteria));
    }
}
