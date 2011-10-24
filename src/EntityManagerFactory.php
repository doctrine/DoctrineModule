<?php
namespace SpiffyDoctrine;

class EntityManagerFactory
{
    public static function create($name, Container $container)
    {
        return $container->getEntityManager($name);
    }
}