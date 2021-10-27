<?php

declare(strict_types=1);

namespace DoctrineModule\Service\Authentication;

use DoctrineModule\Authentication\Storage\ObjectRepository;
use DoctrineModule\Options\Authentication;
use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use RuntimeException;

use function get_class;
use function is_string;
use function sprintf;

/**
 * Factory to create authentication storage object.
 *
 * @link    http://www.doctrine-project.org/
 */
class StorageFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = $this->getOptions($container, 'authentication');

        if (! $options instanceof Authentication) {
            throw new RuntimeException(sprintf(
                'Invalid options received, expected %s, got %s.',
                Authentication::class,
                get_class($options)
            ));
        }

        $objectManager = $options->getObjectManager();
        if (is_string($objectManager)) {
            $options->setObjectManager($container->get($objectManager));
        }

        $storage = $options->getStorage();
        if (is_string($storage)) {
            $options->setStorage($container->get($storage));
        }

        return new ObjectRepository($options);
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     *
     * @return ObjectRepository
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ObjectRepository::class);
    }

    public function getOptionsClass(): string
    {
        return Authentication::class;
    }
}
