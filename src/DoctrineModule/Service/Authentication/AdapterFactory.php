<?php

declare(strict_types=1);

namespace DoctrineModule\Service\Authentication;

use DoctrineModule\Authentication\Adapter\ObjectRepository;
use DoctrineModule\Options\Authentication;
use DoctrineModule\Service\ServiceFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use function assert;
use function is_string;

/**
 * Factory to create authentication adapter object.
 *
 * @link    http://www.doctrine-project.org/
 */
class AdapterFactory extends ServiceFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = $this->getOptions($container, 'authentication');
        assert($options instanceof Authentication);

        $objectManager = $options->getObjectManager();
        if (is_string($objectManager)) {
            $options->setObjectManager($container->get($objectManager));
        }

        return new ObjectRepository($options);
    }

    /**
     * {@inheritDoc}
     *
     * @return ObjectRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ObjectRepository::class);
    }

    public function getOptionsClass() : string
    {
        return 'DoctrineModule\Options\Authentication';
    }
}
