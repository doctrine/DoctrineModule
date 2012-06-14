<?php

namespace DoctrineModule\Service;

use InvalidArgumentException;
use RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DriverFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function factory(ServiceLocatorInterface $serviceLocator, array $config)
    {
        if (!isset($config['type'])) {
            throw new InvalidArgumentException('Drivers must specify a type');
        }

        if (!class_exists($config['type'])) {
            throw new InvalidArgumentException(sprintf(
                'Driver with type "%s" could not be found',
                $config['type']
            ));
        }

        // Not all drivers (DriverChain) require paths.
        $paths = isset($config['paths']) ? $config['paths'] : array();

        // Special options for AnnotationDrivers.
        if (($config['type'] == 'Doctrine\ORM\Mapping\Driver\AnnotationDriver') ||
            (is_subclass_of($config['type'], 'Doctrine\ORM\Mapping\Driver\AnnotationDriver')))
        {
            $cache = isset($config['cache']) ? $config['cache'] : 'array';

            $reader = new \Doctrine\Common\Annotations\AnnotationReader;
            $reader = new \Doctrine\Common\Annotations\CachedReader(
                new \Doctrine\Common\Annotations\IndexedReader($reader),
                $serviceLocator->get("doctrine.cache.{$cache}")
            );

            $driver = new $config['type']($reader, $paths);
        } else {
            $driver = new $config['type']($paths);
        }

        // File-drivers allow extensions.
        if (isset($config['extension']) && method_exists($driver, 'setFileExtension')) {
            $driver->setFileExtension($config['extension']);
        }

        // Extra post-create options for DriverChain.
        if ($driver instanceof \Doctrine\ORM\Mapping\Driver\DriverChain && isset($config['drivers'])) {
            $drivers = $config['drivers'];
            if (!is_array($drivers)) {
                $drivers = array($drivers);
            }

            foreach($drivers as $namespace => $driverName) {
                $doctrine = $serviceLocator->get('Configuration');
                $doctrine = $doctrine['doctrine'];
                $config   = isset($doctrine['driver'][$driverName]) ? $doctrine['driver'][$driverName] : null;

                if (null === $config) {
                    throw new RuntimeException(sprintf(
                        'Driver with name "%s" could not be found in "doctrine.driver".',
                        $driverName
                    ));
                }

                $driver->addDriver(self::factory($serviceLocator, $config), $namespace);
            }
        }

        return $driver;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $doctrine = $serviceLocator->get('Configuration');
        $doctrine = $doctrine['doctrine'];
        $config   = isset($doctrine['driver'][$this->name]) ? $doctrine['driver'][$this->name] : null;

        if (null === $config) {
            throw new RuntimeException(sprintf(
                'Driver with name "%s" could not be found in "doctrine.driver".',
                $this->name
            ));
        }

        return self::factory($serviceLocator, $config);
    }
}