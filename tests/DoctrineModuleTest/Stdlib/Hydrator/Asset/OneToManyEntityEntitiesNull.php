<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class OneToManyEntityEntitiesNull extends OneToManyEntity
{

    public function __construct()
    {
        // do nothing here to leave $entities null
    }

    public function addEntities(Collection $entities, $modifyValue = true)
    {
        if($this->entities !== null) {
            parent::addEntities($entities, $modifyValue = true);
        } else {
            $this->entities = $entities;
        }
    }

    public function getEntities($modifyValue = true)
    {
        return $this->entities;
    }
}
