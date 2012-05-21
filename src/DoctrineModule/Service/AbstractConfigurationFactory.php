<?php

namespace DoctrineModule\Service;

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
            $events->addIdentifiers(array(__CLASS__));

            $this->events = $events;
        }
        return $this->events;
    }

    protected function getDriverChain(ServiceLocatorInterface $sl, $config)
    {
        if (null === $this->chain) {
            $events = $this->events($sl);
            $chain  = new DriverChain;

            $collection = $events->trigger('loadDrivers', $sl, array('config' => $config));
            foreach($collection as $response) {
                foreach($response as $namespace => $driver) {
                    $chain->addDriver($driver, $namespace);
                }
            }

            $this->chain = $chain;
        }

        return $this->chain;
    }
}