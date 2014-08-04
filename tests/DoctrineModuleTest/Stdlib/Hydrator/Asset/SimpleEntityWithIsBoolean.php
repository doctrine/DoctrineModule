<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class SimpleEntityWithIsBoolean
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $isActive;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = (bool) $isActive;
    }

    public function isActive()
    {
        return $this->isActive;
    }
}
