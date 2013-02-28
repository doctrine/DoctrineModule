<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DoctrineModuleTest\Stdlib\Hydrator\Asset\SimpleEntity;

class OneToManyEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Collection
     */
    protected $entities;


    public function __construct()
    {
        $this->entities = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function addEntitie(SimpleEntity $entities, $modifyValue = true)
    {
        // Modify the value to illustrate the difference between by value and by reference
        if($modifyValue) {
            $entities->setField('Modified from addEntities adder', false);
        }

        $this->entities[] = $entities;

        return $this;
    }

    public function removeEntitie(SimpleEntity $entities)
    {
        $this->entities->removeElement($entities);
    }

    public function getEntities($modifyValue = true)
    {
        // Modify the value to illustrate the difference between by value and by reference
        if ($modifyValue) {
            foreach ($this->entities as $entity) {
                $entity->setField('Modified from getEntities getter', false);
            }
        }

        return $this->entities;
    }
}
