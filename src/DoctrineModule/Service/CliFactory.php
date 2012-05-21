<?php

namespace DoctrineModule\Service;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class CliFactory implements FactoryInterface
{
    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $events;

    /**
     * @var \Symfony\Component\Console\Helper\HelperSet
     */
    protected $helperSet;

    /**
     * @var array
     */
    protected $commands = array();

    public function events(ServiceManager $sm)
    {
        if (null === $this->events) {
            $events = $sm->get('EventManager');
            $events->addIdentifiers(array(
                __CLASS__,
                'doctrine'
            ));

            $this->events = $events;
        }
        return $this->events;
    }

    public function createService(ServiceLocatorInterface $sl)
    {
        $cli = new Application;
        $cli->setName('DoctrineModule Command Line Interface');
        $cli->setVersion('dev-master');
        $cli->setHelperSet($this->getHelperSet($sl));

        // Load commands using event
        $this->events($sl)->trigger('loadCliCommands', $cli, array('ServiceManager' => $sl));

        return $cli;
    }

    protected function getHelperSet(ServiceManager $sm)
    {
        if (null === $this->helperSet) {
            $helperSet  = new HelperSet;
            $collection = $this->events($sm)->trigger('loadCliHelperSet', $helperSet, array('ServiceManager' => $sm));

            $this->helperSet = $helperSet;
        }

        return $this->helperSet;
    }
}