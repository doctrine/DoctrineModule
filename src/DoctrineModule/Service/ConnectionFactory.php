<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConnectionFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $doctrine = $serviceLocator->get('Configuration');
        $doctrine = $doctrine['doctrine'];
        $config   = isset($doctrine['connection'][$this->name]) ? $doctrine['connection'][$this->name] : null;

        if (null === $config) {
            throw new RuntimeException(sprintf(
                'Connection with name "%s" could not be found in "doctrine.connection".',
                $this->name
            ));
        }

        $configuration = $serviceLocator->get("doctrine.configuration.{$config['configuration']}");
        $eventManager  = $serviceLocator->get("doctrine.eventmanager.{$config['eventmanager']}");

        return DriverManager::getConnection($config, $configuration, $eventManager);
    }
}