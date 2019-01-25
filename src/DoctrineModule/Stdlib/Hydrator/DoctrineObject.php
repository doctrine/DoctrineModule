<?php

namespace DoctrineModule\Stdlib\Hydrator;

use Doctrine\Zend\Hydrator\DoctrineObject as ZendDoctrineObject;
use DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByReference;
use DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByValue;

/**
 * This hydrator has been completely refactored for DoctrineModule 0.7.0. It provides an easy and powerful way
 * of extracting/hydrator objects in Doctrine, by handling most associations types.
 *
 * Starting from DoctrineModule 0.8.0, the hydrator can be used multiple times with different objects
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 *
 * @deprecated Please use \Doctrine\Zend\Hydrator\DoctrineObject instead.
 */
class DoctrineObject extends ZendDoctrineObject
{
    /**
     * @var string
     */
    protected $defaultByValueStrategy = AllowRemoveByValue::class;

    /**
     * @var string
     */
    protected $defaultByReferenceStrategy = AllowRemoveByReference::class;
}
