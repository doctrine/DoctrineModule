<?php
declare(strict_types=1);

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class OneToManyReferencingIdentifierEntity
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var Collection|OneToManyReferencingIdentifierEntityReferencingBack[]
     */
    public $toMany;

    public function __construct()
    {
        $this->toMany = new ArrayCollection();
    }

    public function addToMany(Collection $entities)
    {
        foreach ($entities as $toMany) {
            if ($this->toMany->contains($toMany)) {
                return;
            }

            $this->toMany->add($toMany);
        }
    }

    public function removeToMany(Collection $entities)
    {
        foreach ($entities as $toMany) {
            if (!$this->toMany->contains($toMany)) {
                return;
            }

            $this->toMany->removeElement($toMany);
        }
    }

    public function getToMany(): Collection
    {
        return $this->toMany;
    }
}
