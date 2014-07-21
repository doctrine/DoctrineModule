<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Zend\Stdlib\Hydrator\Filter\FilterInterface;

class SimpleFilter implements FilterInterface
{
    /**
     * Should return true, if the given filter
     * does not match
     *
     * @param string $property The name of the property
     * @return bool
     */
    public function filter($property)
    {
        return $property !== 'password';
    }
}
