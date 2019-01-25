<?php

namespace DoctrineModule\Stdlib\Hydrator\Filter;

use Doctrine\Zend\Hydrator\Filter\PropertyName as ZendPropertyName;

/**
 * Provides a filter to restrict returned fields by whitelisting or
 * blacklisting property names.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Liam O'Boyle <liam@ontheroad.net.nz>
 *
 * @deprecated Please use \Doctrine\Zend\Hydrator\Filter\PropertyName instead.
 */
class PropertyName extends ZendPropertyName
{
}
