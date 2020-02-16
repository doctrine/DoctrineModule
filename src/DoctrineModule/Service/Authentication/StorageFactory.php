<?php

declare(strict_types=1);

namespace DoctrineModule\Service\Authentication;

use DoctrineModule\Authentication\Storage\ObjectRepository;
use DoctrineModule\Options\Authentication;
use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use function assert;
use function is_string;

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
        assert($options instanceof Authentication);

        if (is_string($objectManager = $options->getObjectManager())) {
            $options->setObjectManager($container->get($objectManager));
        }

        if (is_string($storage = $options->getStorage())) {
            $options->setStorage($container->get($storage));
        }

        return new ObjectRepository($options);
    }

    /**
     * {@inheritDoc}
     *
     * @return ObjectRepository
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ObjectRepository::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Authentication';
    }
}
