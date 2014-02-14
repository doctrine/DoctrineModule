<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use SplFixedArray;

class SimpleEntityWithCustomType
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var SplFixedArray
     */
    protected $fixedArray;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setFixedArray(SplFixedArray $fixedArray = null)
    {
        $this->fixedArray = $fixedArray;
    }

    public function getFixedArray()
    {
        return $this->fixedArray;
    }
}
