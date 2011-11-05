<?php
namespace SpiffyDoctrine;

use Doctrine\Common\Annotations\AnnotationRegistry,
    Zend\Config\Config,
    Zend\EventManager\Event,
    Zend\Module\Manager;

class Module
{
    
    public function init(Manager $moduleManager)
    {
        $this->initAutoloader();
        $this->initDoctrineAnnotations();
    }
    
    public function initDoctrineAnnotations()
    {
        $libfile = __DIR__ . '/vendor/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
        if (file_exists($libfile)) {
            AnnotationRegistry::registerFile($libfile);
        } else {
            @require_once 'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
            if (!class_exists('Doctrine\ORM\Mapping\Entity')) {
                throw new \Exception('Failed to register annotations. Ensure Doctrine is on your include path.');
            }
        }
    }
    
    public function initAutoloader()
    {
        require __DIR__ . '/autoload_register.php';
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/configs/module.config.php';
    }
    
    public function getClassmap()
    {
        return include __DIR__ . '/classmap.php';
    }
    
}