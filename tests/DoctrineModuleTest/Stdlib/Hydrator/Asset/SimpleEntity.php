<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;


class SimpleEntity
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

    public function setField($field, $modifyValue = true)
    {
        // Modify the value to illustrate the difference between by value and by reference
        if ($modifyValue) {
            $this->field = "From setter: $field";
        } else {
            $this->field = $field;
        }
    }

    public function getField($modifyValue = true)
    {
        // Modify the value to illustrate the difference between by value and by reference
        if ($modifyValue) {
            return "From getter: $this->field";
        } else {
            return $this->field;
        }
    }
}
