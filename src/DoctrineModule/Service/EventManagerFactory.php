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
        /** @var $options \DoctrineModule\Options\EventManager */
        $options = $this->getOptions($sl, 'eventmanager');
        $evm     = new EventManager;

        foreach($options->getSubscribers() as $subscriber) {
            if (is_subclass_of($subscriber, 'Doctrine\Common\EventSubscriber')) {
                $evm->addEventSubscriber(new $subscriber);
            } else {
                $evm->addEventSubscriber($sl->get($subscriber));
            }
        }

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