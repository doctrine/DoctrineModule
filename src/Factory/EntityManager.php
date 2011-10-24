<?php
namespace SpiffyDoctrine\Factory;
use SpiffyDoctrine\Container\Container;

class EntityManager
{
    public static function create($name, Container $container)
    {
        return $container->getEntityManager($name);
    }
}