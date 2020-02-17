<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use Doctrine\Common\Annotations;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use DoctrineModule\Options\Driver as DriverOptions;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use function class_exists;
use function get_class;
use function is_array;
use function is_subclass_of;
use function sprintf;

/**
 * MappingDriver ServiceManager factory
 *
 * @link    http://www.doctrine-project.org/
 */
class DriverFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     *
     * @return MappingDriver
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = $this->getOptions($container, 'driver');

        return $this->createDriver($container, $options);
    }

    /**
     * {@inheritDoc}
     *
     * @return MappingDriver
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, MappingDriver::class);
    }

    public function getOptionsClass() : string
    {
        return 'DoctrineModule\Options\Driver';
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function createDriver(ContainerInterface $container, DriverOptions $options) : MappingDriver
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
        if ($class === 'Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver'
            || is_subclass_of($class, 'Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver')
        ) {
            $reader = new Annotations\AnnotationReader();
            $reader = new Annotations\CachedReader(
                new Annotations\IndexedReader($reader),
                $container->get($options->getCache())
            );
            $driver = new $class($reader, $paths);
        } else {
            $driver = new $class($paths);
        }

        if ($options->getExtension() && $driver instanceof FileDriver) {
            $locator = $driver->getLocator();

            if (get_class($locator) !== 'Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator') {
                throw new InvalidArgumentException(
                    sprintf(
                        'Discovered file locator for driver of type "%s" is an instance of "%s". This factory '
                        . 'supports only the DefaultFileLocator when an extension is set for the file locator',
                        get_class($driver),
                        get_class($locator)
                    )
                );
            }

            $driver->setLocator(new DefaultFileLocator($locator->getPaths(), $options->getExtension()));
        }

        // Extra post-create options for DriverChain.
        if ($driver instanceof MappingDriverChain && $options->getDrivers()) {
            $drivers = $options->getDrivers();

            if (! is_array($drivers)) {
                $drivers = [$drivers];
            }

            foreach ($drivers as $namespace => $driverName) {
                if ($driverName === null) {
                    continue;
                }

                $options = $this->getOptions($container, 'driver', $driverName);
                $driver->addDriver($this->createDriver($container, $options), $namespace);
            }
        }

        return $driver;
    }
}
