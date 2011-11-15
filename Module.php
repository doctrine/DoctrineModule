<?php
namespace SpiffyDoctrine;

use Doctrine\Common\Annotations\AnnotationRegistry,
    Zend\Config\Config,
    Zend\EventManager\Event,
    Zend\Loader\AutoloaderFactory,
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
    
    protected function initAutoloader($env = null)
    {
        AutoloaderFactory::factory(array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        ));
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/configs/module.config.php';
    }
}