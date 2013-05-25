<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class ContextStrategy implements StrategyInterface
{
    public function extract($value, $object = null)
    {
        return (string) $value . $object->getField();
    }

    public function hydrate($value, $data = null)
    {
        return $value . $data['field'];
    }
}
