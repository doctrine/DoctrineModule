<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use DoctrineModule\Options\EventManager as EventManagerOptions;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use RuntimeException;

use function class_exists;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Factory responsible for creating EventManager instances
 */
class EventManagerFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = $this->getOptions($container, 'eventmanager');

        if (! $options instanceof EventManagerOptions) {
            throw new RuntimeException(sprintf(
                'Invalid options received, expected %s, got %s.',
                EventManagerOptions::class,
                get_class($options)
            ));
        }

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
     *
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, EventManager::class);
    }

    /**
     * Get the class name of the options associated with this factory.
     */
    public function getOptionsClass(): string
    {
        return EventManagerOptions::class;
    }
}
