<?php

namespace DoctrineModule\Service;

use DoctrineModule\Version;
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
        $cli->setVersion(Version::VERSION);
        $cli->setHelperSet(new HelperSet);

        // Load commands using event
        $this->events($sl)->trigger('loadCli.post', $cli, array('ServiceManager' => $sl));

        return $cli;
    }
}