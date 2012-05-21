<?php

namespace DoctrineModule\Service;

use Doctrine\ORM\Mapping\Driver\DriverChain;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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

    public function events($sl)
    {
        if (null === $this->events) {
            exit;
            $events = new EventManager;
            $events->setIdentifiers(array(
                __CLASS__,
                'Doctrine\ORM\Configuration',
                'doctrine_orm_configuration',
                'DoctrineORMModule'
            ));

            $this->events = $events;
        }
        return $this->events;
    }

    protected function getDriverChain(ServiceLocatorInterface $sl, $config)
    {
        if (null === $this->chain) {
            $events = $this->events($sl);
            $chain  = new DriverChain;

            // TODO: Temporary workaround for EventManagerFactory. Remove when file is patched.
            $events->setSharedManager($sl->get('ModuleManager')->events()->getSharedManager());

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