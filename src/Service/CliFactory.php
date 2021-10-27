<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * CLI Application ServiceManager factory responsible for instantiating a Symfony CLI application
 *
 * @link    http://www.doctrine-project.org/
 */
class CliFactory implements FactoryInterface
{
    /** @var EventManagerInterface */
    protected $events;

    /** @var HelperSet */
    protected $helperSet;

    /** @var mixed[] */
    protected $commands = [];

    public function getEventManager(ContainerInterface $container): EventManagerInterface
    {
        if ($this->events === null) {
            $events = $container->get('EventManager');

            $events->addIdentifiers([self::class, 'doctrine']);

            $this->events = $events;
        }

        return $this->events;
    }

    /**
     * {@inheritDoc}
     *
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $cli = new Application();
        $cli->setName('DoctrineModule Command Line Interface');
        $cli->setHelperSet(new HelperSet());
        $cli->setCatchExceptions(true);
        $cli->setAutoExit(false);

        // Load commands using event
        $this->getEventManager($container)->trigger('loadCli.post', $cli, ['ServiceManager' => $container]);

        return $cli;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     *
     * @return Application
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Application::class);
    }
}
