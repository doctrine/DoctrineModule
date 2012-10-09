<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\TestAsset;

class HydratorStrategy
{
    public function extract($value)
    {
        return $value;
    }

    public function hydrate($value)
    {
        return 'MODIFIED';
    }
}