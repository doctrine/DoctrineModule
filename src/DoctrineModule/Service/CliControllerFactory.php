<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use DoctrineModule\Controller\CliController;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Console\Application;

/**
 * Factory responsible of instantiating an {@see \DoctrineModule\Controller\CliController}
 *
 * @link    http://www.doctrine-project.org/
 */
class CliControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * {@inheritDoc}
     *
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when creating a service.
     * @throws ContainerException if any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null) : object
    {
        $application = $container->get('doctrine.cli');

        return new CliController($application);
    }

    /**
     * {@inheritDoc}
     *
     * @return CliController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container->getServiceLocator(), CliController::class);
    }
}
