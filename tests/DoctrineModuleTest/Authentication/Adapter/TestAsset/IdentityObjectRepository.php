<?php

namespace DoctrineModuleTest\Authentication\Adapter\TestAsset;

use Doctrine\Common\Persistence\ObjectRepository;

abstract class IdentityObjectRepository implements ObjectRepository
{
    abstract public function findByEmail($email);
} 