<?php
namespace DoctrineModule\Service\Authentication;

use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create authentication service object.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AuthenticationServiceFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AuthenticationService(
            $container->get('doctrine.authenticationstorage.' . $this->getName()),
            $container->get('doctrine.authenticationadapter.' . $this->getName())
        );
    }

    /**
     *
     * @param \Laminas\ServiceManager\ServiceLocatorInterface $container
     * @return \Laminas\Authentication\AuthenticationService
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, AuthenticationService::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
