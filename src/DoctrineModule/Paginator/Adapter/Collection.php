<?php

namespace DoctrineModule\Paginator\Adapter;

use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Zend\Paginator\Adapter\AdapterInterface;

class Collection implements AdapterInterface
{
    /**
     * @var DoctrineCollection
     */
    protected $collection;


    /**
     * @param DoctrineCollection $collection
     */
    public function __construct(DoctrineCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Returns a collection of items for a page.
     *
     * @param  integer $offset           Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return $this->collection->slice($offset - 1, $itemCountPerPage);
    }

    /**
     * Returns the total count of elements
     * 
     * @return integer
     */
    public function count()
    {
        return count($this->collection);
    }
}
