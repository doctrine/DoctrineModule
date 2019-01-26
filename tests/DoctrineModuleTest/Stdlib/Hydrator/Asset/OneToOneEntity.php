<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use DateTime;

class OneToOneEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ByValueDifferentiatorEntity
     */
    protected $toOne;

    /**
     * @var DateTime
     */
    protected $createdAt;


    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setToOne(ByValueDifferentiatorEntity $entity = null, $modifyValue = true)
    {
        // Modify the value to illustrate the difference between by value and by reference
        if ($modifyValue && $entity !== null) {
            $entity->setField('Modified from setToOne setter', false);
        }

        $this->toOne = $entity;
    }

    public function getToOne($modifyValue = true)
    {
        // Make some modifications to the association so that we can demonstrate difference between
        // by value and by reference
        if ($modifyValue) {
            $this->toOne->setField('Modified from getToOne getter', false);
        }

        return $this->toOne;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
