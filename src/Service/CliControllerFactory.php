<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use DoctrineModule\Controller\CliController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating an {@see \DoctrineModule\Controller\CliController}
 *
 * @deprecated 4.2.0 Through the deprecation of \DoctrineModule\Controller\CliController, this class is not needed
 *                   anymore and will be removed in 5.0.0.
 */
class CliControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): object
    {
        $application = $container->get('doctrine.cli');

        return new CliController($application);
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     *
     * @return CliController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, CliController::class);
    }
}
