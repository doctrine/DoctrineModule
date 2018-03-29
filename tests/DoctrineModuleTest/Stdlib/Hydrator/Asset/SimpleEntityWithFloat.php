<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class SimpleEntityWithFloat
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var float
     */
    protected $floatField;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setFloatField($value)
    {
        $this->floatField = $value;

        return $this;
    }

    public function getFloatField()
    {
        return $this->floatField;
    }
}
