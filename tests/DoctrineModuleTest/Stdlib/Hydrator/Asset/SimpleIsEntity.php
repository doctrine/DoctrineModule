<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class SimpleIsEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $done;

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

    public function setDone($done)
    {
        $this->done = (bool) $done;
    }

    public function isDone()
    {
        return $this->done;
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
