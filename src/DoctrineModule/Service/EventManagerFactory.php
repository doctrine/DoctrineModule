<?php

namespace DoctrineModule\Service;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible for creating EventManager instances
 */
class EventManagerFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $options \DoctrineModule\Options\EventManager */
        $options      = $this->getOptions($container, 'eventmanager');
        $eventManager = new EventManager();

        foreach ($options->getSubscribers() as $subscriberName) {
            $subscriber = $subscriberName;

            if (is_string($subscriber)) {
                if ($container->has($subscriber)) {
                    $subscriber = $container->get($subscriber);
                } elseif (class_exists($subscriber)) {
                    $subscriber = new $subscriber();
                }
            }

            if ($subscriber instanceof EventSubscriber) {
                $eventManager->addEventSubscriber($subscriber);
                continue;
            }

            $subscriberType = is_object($subscriberName) ? get_class($subscriberName) : $subscriberName;

            throw new InvalidArgumentException(
                sprintf(
                    'Invalid event subscriber "%s" given, must be a service name, '
                    . 'class name or an instance implementing Doctrine\Common\EventSubscriber',
                    is_string($subscriberType) ? $subscriberType : gettype($subscriberType)
                )
            );
        }

        return $eventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, EventManager::class);
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
