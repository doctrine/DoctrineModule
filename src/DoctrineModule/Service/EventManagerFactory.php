<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Doctrine\Common\EventManager;
use DoctrineModule\Service\AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventManagerFactory extends AbstractFactory
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $options = $this->getOptions($sl, 'eventmanager');
        $evm     = new EventManager;

        // todo: implement event manager configuration

        return $evm;
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\EventManager';
    }
}