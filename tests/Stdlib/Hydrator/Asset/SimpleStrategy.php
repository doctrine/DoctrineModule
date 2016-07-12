<?php
namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class SimpleStrategy implements StrategyInterface
{
    public function extract($value)
    {
        return 'modified while extracting';
    }

    public function hydrate($value)
    {
        return 'modified while hydrating';
    }
}
