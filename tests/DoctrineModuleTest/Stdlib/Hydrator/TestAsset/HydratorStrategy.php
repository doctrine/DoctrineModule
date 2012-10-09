<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\TestAsset;

use Zend\Stdlib\Hydrator\Strategy\DefaultStrategy;

class HydratorStrategy extends DefaultStrategy
{
    public function hydrate($value)
    {
        return 'MODIFIED';
    }
}