<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class SimpleEntityWithEmbeddable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Embeddable
     */
    protected $embedded;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Embeddable
     */
    public function getEmbedded()
    {
        return $this->embedded;
    }

    /**
     * @param Embeddable $embedded
     */
    public function setEmbedded(Embeddable $embedded)
    {
        $this->embedded = $embedded;
    }
}
