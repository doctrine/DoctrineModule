<?php

namespace DoctrineModule\Service;

use InvalidArgumentException;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use DoctrineModule\Service\AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible for creating EventManager instances
 */
class EventManagerFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var $options \DoctrineModule\Options\EventManager */
        $options      = $this->getOptions($sl, 'eventmanager');
        $eventManager = new EventManager();

        foreach($options->getSubscribers() as $subscriberName) {
            $subscriber = $subscriberName;

            if (is_string($subscriber)) {
                if ($sl->has($subscriber)) {
                    $subscriber = $sl->get($subscriber);
                } else if (class_exists($subscriber)) {
                    $subscriber = new $subscriber();
                }
            }

            if ($subscriber instanceof EventSubscriber) {
                $eventManager->addEventSubscriber($subscriber);
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'Invalid event subscriber "%s" given, must be a service name, '
                . 'class name or an instance implementing Doctrine\Common\EventSubscriber',
                is_object($subscriberName)
                    ? get_class($subscriberName)
                    : (is_string($subscriberName) ? $subscriberName : gettype($subscriber))
            ));
        }

        return $eventManager;
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