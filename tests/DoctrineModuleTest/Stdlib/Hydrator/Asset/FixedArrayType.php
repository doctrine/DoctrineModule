<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Doctrine\DBAL\Types\StringType;
use DoctrineModule\Stdlib\Hydrator\TypeConversionInterface;
use SplFixedArray;

class FixedArrayType extends StringType implements TypeConversionInterface
{
    public function convertToHydratorValue($value)
    {
        return new SplFixedArray(1);
    }
}
