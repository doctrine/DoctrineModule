<?php

namespace DoctrineModule\Service;

use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * CLI Application ServiceManager factory responsible for instantiating a Symfony CLI application
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class CliFactory implements FactoryInterface
{
    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $events;

    /**
     * @var \Symfony\Component\Console\Helper\HelperSet
     */
    protected $helperSet;

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @param  ContainerInterface $container
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager(ContainerInterface $container)
    {
        if (null === $this->events) {
            /* @var $events \Zend\EventManager\EventManagerInterface */
            $events = $container->get('EventManager');

            $events->addIdentifiers([__CLASS__, 'doctrine']);

            $this->events = $events;
        }

        return $this->events;
    }

    /**
     * {@inheritDoc}
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $cli = new Application;
        $cli->setName('DoctrineModule Command Line Interface');
        $cli->setHelperSet(new HelperSet);
        $cli->setCatchExceptions(true);
        $cli->setAutoExit(false);

        // Load commands using event
        $this->getEventManager($container)->trigger('loadCli.post', $cli, ['ServiceManager' => $container]);

        return $cli;
    }

    /**
     * {@inheritDoc}
     * @return Application
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, Application::class);
    }
}
