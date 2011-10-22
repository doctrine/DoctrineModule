<?php
namespace SpiffyDoctrine;

use Doctrine\Common\Annotations\AnnotationRegistry,
    InvalidArgumentException,
    Zend\Config\Config;

class Module
{
    public function init()
    {
        $this->initAutoloader();
        $this->initDoctrineAnnotationMappings();
    }
    
    public function initAutoloader()
    {
        require __DIR__ . '/autoload_register.php';
    }
    
    /**
     * Registers the default annotation mapping file provided by Doctrine.
     * In order for this file to be registered, register_default_annotations must
     * be set to true in the application.config.php file.
     * 
     * Also, the root path to where Doctrine is located is required and can be 
     * specified by setting doctrine_path. By default, it is located in lib/Doctrine of
     * this module. 
     */
    public function initDoctrineAnnotationMappings()
    {
        $config = $this->getConfig(APPLICATION_ENV);
        if ($config->doctrine['register_default_annotations']) {
            if (!($doctrinePath = $config->doctrine['doctrine_path'])) {
                throw new \InvalidArgumentException('doctrine_path must be set');
            }
            AnnotationRegistry::registerFile($doctrinePath . 'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
        }
    }

    public function getConfig($env = null)
    {
        $config = new Config(include __DIR__ . '/etc/module.config.php');
        if (null === $env) {
            return $config;
        }
        if (!isset($config->{$env})) {
            throw new InvalidArgumentException(sprintf(
                'Unrecognized environment "%s" provided to "%s"',
                $env,
                __METHOD__
            ));
        }
		
        return $config->{$env};
    }

    public function getClassmap()
    {
        return include __DIR__ . '/classmap.php';
    }
}