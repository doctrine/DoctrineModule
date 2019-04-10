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
     * @var Collection
     */
    public $toMany;

    public function __construct()
    {
        $this->toMany = new ArrayCollection();
    }
}
