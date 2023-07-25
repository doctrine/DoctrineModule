<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use DoctrineModule\Options\EventManager as EventManagerOptions;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function class_exists;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Factory responsible for creating EventManager instances
 */
final class EventManagerFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array|null $options = null)
    {
        $options = $this->getOptions($container, 'eventmanager');

        if (! $options instanceof EventManagerOptions) {
            throw new RuntimeException(sprintf(
                'Invalid options received, expected %s, got %s.',
                EventManagerOptions::class,
                $options::class,
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

            $subscriberType = is_object($subscriberName) ? $subscriberName::class : $subscriberName;

            throw new InvalidArgumentException(
                sprintf(
                    'Invalid event subscriber "%s" given, must be a service name, '
                    . 'class name or an instance implementing Doctrine\Common\EventSubscriber',
                    is_string($subscriberType) ? $subscriberType : gettype($subscriberType),
                ),
            );
        }

        return $eventManager;
    }

    /**
     * Get the class name of the options associated with this factory.
     */
    public function getOptionsClass(): string
    {
        return EventManagerOptions::class;
    }
}
