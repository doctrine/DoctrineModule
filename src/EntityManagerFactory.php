<?php
namespace SpiffyDoctrine;

class EntityManagerFactory
{
    /**
     * Doctrine container instance.
     * 
     * @var Container
     */
    protected static $_container;
    
    public static function setContainer(Container $container)
    {
        self::$_container = $container;
    }
    
    public static function getInstance($name)
    {
        print_r($connection);
        exit;
    }
}