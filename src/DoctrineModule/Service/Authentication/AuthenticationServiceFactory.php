<?php

declare(strict_types=1);

namespace DoctrineModule\Service\Authentication;

use BadMethodCallException;
use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create authentication service object.
 *
 * @link    http://www.doctrine-project.org/
 */
class AuthenticationServiceFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new AuthenticationService(
            $container->get('doctrine.authenticationstorage.' . $this->getName()),
            $container->get('doctrine.authenticationadapter.' . $this->getName())
        );
    }

    public function createService(ServiceLocatorInterface $container) : AuthenticationService
    {
        return $this($container, AuthenticationService::class);
    }

    public function getOptionsClass() : string
    {
        throw new BadMethodCallException('Not implemented');
    }
}
