<?php

namespace DoctrineModule\Service;

use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use DoctrineModule\Mvc\Router\Console\SymfonyCliV2;
use DoctrineModule\Mvc\Router\Console\SymfonyCliV3;
use Interop\Container\ContainerInterface;
use Zend\Mvc\Router\Console\RouteMatch as V2RouteMatch;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating {@see \DoctrineModule\Mvc\Router\Console\SymfonyCli}
 */
class SymfonyCliRouteFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $application \Symfony\Component\Console\Application */
        $application = $container->get('doctrine.cli');

        $symfonyCli = class_exists(V2RouteMatch::class) ? SymfonyCliV2::class : SymfonyCliV3::class;

        return new $symfonyCli(
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
