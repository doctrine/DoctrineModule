<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;


class ContextEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $field;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
    }
}
