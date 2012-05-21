<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $doctrine = $serviceLocator->get('Configuration');
        $doctrine = $doctrine['doctrine'];
        $config   = isset($doctrine['cache'][$this->name]) ? $doctrine['cache'][$this->name] : null;

        if (null === $config) {
            throw new RuntimeException(sprintf(
                'Cache with name "%s" could not be found in "doctrine.cache".',
                $this->name
            ));
        }

        $class = null;
        if (is_string($config)) {
            $class  = $config;
            $config = array();
        } else if (is_array($config) && isset($config['class'])) {
            $class = $config['class'];
            unset($config['class']);
        }

        if (!$class) {
            throw new RuntimeException('Cache must have a class name to instantiate');
        }

        $cache = new $class;

        foreach($config as $key => $alias) {
            $mutator = 'set' . ucfirst($key);

            if (method_exists($class, $mutator)) {
                $class->$mutator($serviceLocator->get($alias));
            }
        }

        return $cache;
    }
}