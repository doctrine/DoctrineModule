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
        
        $moduleManager->events()->attach('init.post', array($this, 'initAdditionalAnnotations'), 1000);
    }
    
    /**
     * Initialize the Doctrine default annotation mapping.
     */
    public function initDoctrineAnnotations()
    {
        $libfile = __DIR__ . '/library/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
        if (file_exists($libfile)) {
            AnnotationRegistry::registerFile($libfile);
        } else {
            @require_once 'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
            if (!class_exists('Doctrine\ORM\Mapping\Entity')) {
                throw new \Exception('Failed to register annotations. Ensure Doctrine is on your include path.');
            }
        }
    }
    
    /**
     * Initialize additional annotations from registry files or namespaces. This has to be done
     * as an init.post callback in order to gather the "complete" merged configuration from
     * all modules.
     */
    public function initAdditionalAnnotations(Event $e)
    {
        $moduleManager = $e->getTarget();
        
        $config = $moduleManager->getMergedConfig()->get('spiffy-doctrine');
        if ($sa = $config->get('annotations')) {
            if (is_object($sa->get('namespaces'))) {
                AnnotationRegistry::registerAutoloadNamespaces($sa->get('namespaces')->toArray());
            }
        }
    }
    
    public function initAutoloader()
    {
        require __DIR__ . '/autoload_register.php';
    }
    
    /**
     * @return Config
     */
    public function getConfig()
    {
        return new Config(include __DIR__ . '/configs/module.config.php');
    }
    
    /**
     *
     * @return array
     */
    public function getClassmap()
    {
        return include __DIR__ . '/classmap.php';
    }
    
}