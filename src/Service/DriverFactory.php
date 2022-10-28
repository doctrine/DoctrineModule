<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use Doctrine\Common\Annotations;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver as MongoODMAnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver as MongoODMAttributeDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as ORMAnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver as ORMAttributeDriver;
use Doctrine\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use DoctrineModule\Options\Driver;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use RuntimeException;

use function class_exists;
use function get_class;
use function is_a;
use function sprintf;

/**
 * MappingDriver ServiceManager factory
 */
final class DriverFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     *
     * @return MappingDriver
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = $this->getOptions($container, 'driver');

        if (! $options instanceof Driver) {
            throw new RuntimeException(sprintf(
                'Invalid options received, expected %s, got %s.',
                Driver::class,
                get_class($options)
            ));
        }

        return $this->createDriver($container, $options);
    }

    public function getOptionsClass(): string
    {
        return Driver::class;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function createDriver(ContainerInterface $container, Driver $options): MappingDriver
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
        if (
            $class !== ORMAttributeDriver::class &&
            $class !== MongoODMAttributeDriver::class &&
            (
                is_a($class, ORMAnnotationDriver::class, true) ||
                is_a($class, MongoODMAnnotationDriver::class, true)
            )
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

            if (get_class($locator) !== DefaultFileLocator::class) {
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

            foreach ($drivers as $namespace => $driverName) {
                if ($driverName === null) {
                    continue;
                }

                $options = $this->getOptions($container, 'driver', $driverName);

                if (! $options instanceof Driver) {
                    throw new RuntimeException(sprintf(
                        'Invalid options received, expected %s, got %s.',
                        Driver::class,
                        get_class($options)
                    ));
                }

                $driver->addDriver($this->createDriver($container, $options), $namespace);
            }
        }

        return $driver;
    }
}
