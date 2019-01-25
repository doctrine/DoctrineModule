<?php

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use Doctrine\Zend\Hydrator\Strategy\AllowRemoveByValue as ZendAllowRemoveByValue;

/**
 * When this strategy is used for Collections, if the new collection does not contain elements that are present in
 * the original collection, then this strategy remove elements from the original collection. For instance, if the
 * collection initially contains elements A and B, and that the new collection contains elements B and C, then the
 * final collection will contain elements B and C (while element A will be asked to be removed).
 *
 * This strategy is by value, this means it will use the public API (in this case, adder and remover)
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 *
 * @deprecated Please use \Doctrine\Zend\Hydrator\Strategy\AllowRemoveByValue instead.
 */
class AllowRemoveByValue extends ZendAllowRemoveByValue
{
}
