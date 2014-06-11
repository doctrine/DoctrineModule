<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;


class SubclassEntity extends SimpleEntity
{
    /**
     * @var string
     */
    protected $subclassField;

    public function setSubclassField($subclassField, $modifyValue = true)
    {
        // Modify the value to illustrate the difference between by value and by reference
        if ($modifyValue) {
            $this->subclassField = "From setter: $subclassField";
        } else {
            $this->subclassField = $subclassField;
        }
    }

    public function getSubclassField($modifyValue = true)
    {
        // Modify the value to illustrate the difference between by value and by reference
        if ($modifyValue) {
            return "From getter: $this->subclassField";
        } else {
            return $this->subclassField;
        }
    }
}
