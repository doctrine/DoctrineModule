<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating {@see \DoctrineModule\Mvc\Router\Console\SymfonyCli}
 */
class SymfonyCliRouteFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $application = $container->get('doctrine.cli');

        return new SymfonyCli(
            $application,
            [
                'controller' => 'DoctrineModule\Controller\Cli',
                'action'     => 'cli',
            ]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, SymfonyCli::class);
    }
}
