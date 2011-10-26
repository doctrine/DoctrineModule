<?php
namespace SpiffyDoctrine;

use Doctrine\Common\Annotations\AnnotationRegistry,
    Zend\Config\Config;

class Module
{
    public function init()
    {
        $this->initAutoloader();
    }
    
    public function initAutoloader()
    {
        require __DIR__ . '/autoload_register.php';
    }

    public function getConfig()
    {
        return new Config(include __DIR__ . '/configs/module.config.php');
    }

    public function getClassmap()
    {
        return include __DIR__ . '/classmap.php';
    }
}