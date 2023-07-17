<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * CLI Application ServiceManager factory responsible for instantiating a Symfony CLI application
 */
final class CliFactory implements FactoryInterface
{
    protected EventManagerInterface|null $events = null;

    protected HelperSet $helperSet;

    /** @var mixed[] */
    protected array $commands = [];

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
    public function __invoke(ContainerInterface $container, $requestedName, array|null $options = null)
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
}
