<?php

namespace DoctrineModule\Service;

use InvalidArgumentException;
use Doctrine\Common\Annotations;
use DoctrineModule\Options\Driver as DriverOptions;
use DoctrineModule\Service\AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class DriverFactory extends AbstractFactory
{
    public function createService(ServiceLocatorInterface $sl)
    {
        return $this->createDriver($sl, $this->getOptions($sl, 'driver'));
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Driver';
    }

    /**
     * @param ServiceLocatorInterface $sl
     * @param Driver $options
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function createDriver(ServiceLocatorInterface $sl, DriverOptions $options)
    {
        $class = $options->getClass();

        if (!$class) {
            throw new InvalidArgumentException('Drivers must specify a class');
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf(
                'Driver with type "%s" could not be found',
                $class
            ));
        }

        // Not all drivers (DriverChain) require paths.
        $paths = $options->getPaths();

        // Special options for AnnotationDrivers.
        if (($class == 'Doctrine\ORM\Mapping\Driver\AnnotationDriver') ||
            (is_subclass_of($class, 'Doctrine\ORM\Mapping\Driver\AnnotationDriver')))
        {
            $reader = new Annotations\AnnotationReader;
            $reader = new Annotations\CachedReader(
                new Annotations\IndexedReader($reader),
                $sl->get($options->getCache())
            );
            $driver = new $class($reader, $paths);
        } else {
            $driver = new $class($paths);
        }

        // File-drivers allow extensions.
        if ($options->getExtension() && method_exists($driver, 'setFileExtension')) {
            $driver->setFileExtension($options->getExtension());
        }

        // Extra post-create options for DriverChain.
        if ($driver instanceof \Doctrine\ORM\Mapping\Driver\DriverChain && $options->getDrivers()) {
            $drivers = $options->getDrivers();

            if (!is_array($drivers)) {
                $drivers = array($drivers);
            }

            foreach($drivers as $namespace => $driverName) {
                $options = $this->getOptions($sl, 'driver', $driverName);

                $driver->addDriver($this->createDriver($sl, $options), $namespace);
            }
        }

        return $driver;
    }
}