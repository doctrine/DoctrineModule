<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Doctrine\Common\EventManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventManagerFactory implements FactoryInterface
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
        $config   = isset($doctrine['eventmanager'][$this->name]) ? $doctrine['eventmanager'][$this->name] : null;

        if (null === $config) {
            throw new RuntimeException(sprintf(
                'EventManager with name "%s" could not be found in "doctrine.eventmanager".',
                $this->name
            ));
        }

        $evm = new EventManager;

        // todo: implement event manager configuration

        return $evm;
    }
}