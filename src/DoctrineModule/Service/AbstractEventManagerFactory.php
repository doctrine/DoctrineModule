<?php

namespace DoctrineModule\Service;

use Doctrine\Common\EventManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractEventManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $name = $this->getName();
        $cfg  = $serviceLocator->get('Configuration');
        $cfg  = isset($cfg['doctrine']['eventmanager'][$name]) ?
            $cfg['doctrine']['eventmanager'][$name] :
            null;

        if (null === $cfg) {
            return new EventManager;
        }

        $evm = new EventManager;

        // todo: implement event manager configuration

        return $evm;
    }

    abstract public function getName();
}