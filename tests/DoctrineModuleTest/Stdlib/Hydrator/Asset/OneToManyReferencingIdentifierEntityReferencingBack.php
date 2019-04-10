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
}
