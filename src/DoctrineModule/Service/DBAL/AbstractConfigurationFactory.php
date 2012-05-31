<?php

namespace DoctrineModule\Service\DBAL;

use Doctrine\ORM\Mapping\Driver\DriverChain;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

abstract class AbstractConfigurationFactory implements FactoryInterface
{
    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $events;

    /**
     * @var \Doctrine\ORM\Mapping\Driver\DriverChain
     */
    protected $chain;

    public function events(ServiceManager $sm)
    {
        if (null === $this->events) {
            $events = $sm->get('EventManager');
            $events->addIdentifiers(array($this->getIdentifier()));

            $this->events = $events;
        }
        return $this->events;
    }

    abstract protected function getIdentifier();

    protected function getDriverChain(ServiceManager $sm, $config)
    {
        if (null === $this->chain) {
            $events = $this->events($sm);
            $chain  = new DriverChain;

            $events->trigger(
                'loadDrivers',
                $chain,
                array(
                    'config'          => $config,
                    'service_manager' => $sm
                )
            );

            $this->chain = $chain;
        }

        return $this->chain;
    }
}