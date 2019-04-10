<?php
declare(strict_types=1);

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use DateTimeInterface;

class OneToManyReferencingIdentifierEntityReferencingBack
{

    /**
     * @var OneToManyReferencingIdentifierEntity
     */
    public $toOneReferencingBack;

    /**
     * @var int
     */
    public $secondaryCompositePrimaryKey;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    public function setToOneReferencingBack(OneToManyReferencingIdentifierEntity $toOneReferencingBack): void
    {
        $this->toOneReferencingBack = $toOneReferencingBack;
    }

    public function setSecondaryCompositePrimaryKey(int $secondaryCompositePrimaryKey): void
    {
        $this->secondaryCompositePrimaryKey = $secondaryCompositePrimaryKey;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
