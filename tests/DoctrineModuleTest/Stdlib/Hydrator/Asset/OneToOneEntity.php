<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;


class OneToOneEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var SimpleEntity
     */
    protected $toOne;


    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setToOne(SimpleEntity $entity)
    {
        $this->toOne = $entity;
    }

    public function getToOne()
    {
        return $this->toOne;
    }
}
