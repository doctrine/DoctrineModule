<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Console\Application;
use function assert;

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
        assert($application instanceof Application);

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
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator->getServiceLocator(), SymfonyCli::class);
    }
}
