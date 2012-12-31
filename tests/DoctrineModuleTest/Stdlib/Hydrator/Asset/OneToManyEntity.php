<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    public function addEntities(Collection $entities)
    {
        foreach ($entities as $entity) {
            $this->entities->add($entity);
        }
    }

    public function removeEntities(Collection $entities)
    {
        foreach ($entities as $entity) {
            $this->entities->removeElement($entity);
        }
    }

    public function getEntities()
    {
        return $this->entities;
    }
}
