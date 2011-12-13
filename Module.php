<?php
namespace SpiffyDoctrine;

use Doctrine\Common\Annotations\AnnotationRegistry,
    Zend\EventManager\Event,
    Zend\Module\Consumer\AutoloaderProvider,
    Zend\Module\Manager;

class Module implements AutoloaderProvider
{
    
    public function init(Manager $moduleManager)
    {
        $this->initDoctrineAnnotations();
    }
    
    public function initDoctrineAnnotations()
    {
        $libfile = __DIR__ . '/vendor/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
        if (file_exists($libfile)) {
            AnnotationRegistry::registerFile($libfile);
        } else {
            @include_once 'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
            if (!class_exists('Doctrine\ORM\Mapping\Entity', false)) {
                throw new \Exception(
                    'Failed to register annotations. Ensure Doctrine can be autoloaded or initalize
                    submodules in SpiffyDoctrine'
                );
            }
        }
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
        );
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/configs/module.config.php';
    }
}
