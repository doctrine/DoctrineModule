<?php

namespace DoctrineModule\Service;

use DoctrineModule\Mvc\Router\Console\SymfonyCli;
use Interop\Container\ContainerInterface;
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
