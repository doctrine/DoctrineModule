<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class SimpleEntityWithEmbeddedObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var SimpleEntity
     */
    protected $toOne;

    /**
     * @var EmbeddableObject
     */
    protected $embedded;

    public function getId()
    {
        return $this->id;
    }

    public function getToOne()
    {
        return $this->toOne;
    }

    public function getEmbedded()
    {
        return $this->embedded;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setToOne(SimpleEntity $toOne)
    {
        $this->toOne = $toOne;
    }

    public function setEmbedded(EmbeddableObject $embedded)
    {
        $this->embedded = $embedded;
    }
}
