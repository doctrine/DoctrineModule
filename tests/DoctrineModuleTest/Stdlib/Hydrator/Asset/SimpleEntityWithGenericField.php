<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class SimpleEntityWithGenericField
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var float
     */
    protected $genericField;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setGenericField($value)
    {
        $this->genericField = $value;

        return $this;
    }

    public function getGenericField()
    {
        return $this->genericField;
    }
}
