<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractConnectionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $name = $this->getName();
        $cfg  = $serviceLocator->get('Configuration');
        $cfg  = isset($cfg['doctrine']['connection'][$name]) ?
                    $cfg['doctrine']['connection'][$name] :
                    null;

        if (null === $cfg) {
            throw new RuntimeException(sprintf(
                'Connection with name "%s" could not be found in configuration.',
                $name
            ));
        }

        $configuration = $serviceLocator->get($cfg['configuration']);
        $eventManager  = $serviceLocator->get($cfg['eventmanager']);

        return DriverManager::getConnection($cfg, $configuration, $eventManager);
    }

    /**
     * Get the name of the connection as defined in 'doctrine' => 'connections' config.
     *
     * @abstract
     * @return mixed
     */
    abstract public function getName();
}