<?php

namespace DoctrineModule\Service\DBAL;

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

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $config;

    /**
     * @var string
     */
    protected $eventManager;

    public function __construct($name, $config, $eventManager = null)
    {
        $this->name         = $name;
        $this->config       = $config;
        $this->eventManager = $eventManager ?: new EventManager;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $name = $this->name;
        $cfg  = $serviceLocator->get('Configuration');
        $conn = isset($cfg['doctrine']['connections'][$name]) ? $cfg['doctrine']['connections'][$name] : null;

        if (null === $conn) {
            throw new RuntimeException(sprintf(
                'Connection with name "%s" could not be found in doctrine => connections configuration.',
                $name
            ));
        }

        $config       = $serviceLocator->get($this->config);
        $eventManager = $serviceLocator->get($this->eventManager ?: new EventManager);

        return DriverManager::getConnection($conn, $config, $eventManager);
    }
}