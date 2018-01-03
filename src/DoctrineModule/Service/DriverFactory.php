<?php

namespace DoctrineModule\Service;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Doctrine\Common\Annotations;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
use DoctrineModule\Options\Driver as DriverOptions;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * MappingDriver ServiceManager factory
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class DriverFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     * @return MappingDriver
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $options DriverOptions */
        $options = $this->getOptions($container, 'driver');

        return $this->createDriver($container, $options);
    }
    /**
     * {@inheritDoc}
     * @return MappingDriver
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, MappingDriver::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Driver';
    }

    /**
     * @param  ContainerInterface $container
     * @param  DriverOptions      $options
     * @throws InvalidArgumentException
     * @return MappingDriver
     */
    protected function createDriver(ContainerInterface $container, DriverOptions $options)
    {
        $class = $options->getClass();

        if (! $class) {
            throw new InvalidArgumentException('Drivers must specify a class');
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Driver with type "%s" could not be found', $class));
        }

        // Not all drivers (DriverChain) require paths.
        $paths = $options->getPaths();

        // Special options for AnnotationDrivers.
        if ('Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver' === $class
            || is_subclass_of($class, 'Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver')
        ) {
            $reader = new Annotations\AnnotationReader;
            $reader = new Annotations\CachedReader(
                new Annotations\IndexedReader($reader),
                $container->get($options->getCache())
            );
            /* @var $driver MappingDriver */
            $driver = new $class($reader, $paths);
        } else {
            /* @var $driver MappingDriver */
            $driver = new $class($paths);
        }

        if ($options->getExtension() && $driver instanceof FileDriver) {
            /* @var $driver FileDriver */
            /* @var $locator \Doctrine\Common\Persistence\Mapping\Driver\FileLocator */
            $locator = $driver->getLocator();

            if (get_class($locator) === 'Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator') {
                $driver->setLocator(new DefaultFileLocator($locator->getPaths(), $options->getExtension()));
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'Discovered file locator for driver of type "%s" is an instance of "%s". This factory '
                        . 'supports only the DefaultFileLocator when an extension is set for the file locator',
                        get_class($driver),
                        get_class($locator)
                    )
                );
            }
        }

        // Extra post-create options for DriverChain.
        if ($driver instanceof MappingDriverChain && $options->getDrivers()) {
            /* @var $driver \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain */
            $drivers = $options->getDrivers();

            if (! is_array($drivers)) {
                $drivers = [$drivers];
            }

            foreach ($drivers as $namespace => $driverName) {
                if (null === $driverName) {
                    continue;
                }
                $options = $this->getOptions($container, 'driver', $driverName);
                $driver->addDriver($this->createDriver($container, $options), $namespace);
            }
        }

        return $driver;
    }
}
