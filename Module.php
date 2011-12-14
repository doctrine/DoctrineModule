<?php
namespace SpiffyDoctrine;

use Zend\EventManager\Event,
    Zend\Module\Consumer\AutoloaderProvider,
    Zend\Module\Manager;

class Module implements AutoloaderProvider
{
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
        return include __DIR__ . '/config/module.config.php';
    }
}
