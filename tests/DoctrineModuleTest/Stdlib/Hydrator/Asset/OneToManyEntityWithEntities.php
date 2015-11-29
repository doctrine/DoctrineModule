<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class OneToManyEntityWithEntities extends OneToManyEntity
{
    public function __construct(ArrayCollection $entities = null)
    {
        $this->entities = $entities;
    }

    public function getEntities($modifyValue = true)
    {
        return $this->entities;
    }
}
