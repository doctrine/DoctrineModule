<?php

declare(strict_types=1);

namespace DoctrineModule\Service\Authentication;

use DoctrineModule\Authentication\Adapter\ObjectRepository;
use DoctrineModule\Options\Authentication;
use DoctrineModule\Service\AbstractFactory;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function is_string;
use function sprintf;

/**
 * Factory to create authentication adapter object.
 */
final class AdapterFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array|null $options = null)
    {
        $options = $this->getOptions($container, 'authentication');

        if (! $options instanceof Authentication) {
            throw new RuntimeException(sprintf(
                'Invalid options received, expected %s, got %s.',
                Authentication::class,
                $options::class,
            ));
        }

        $objectManager = $options->getObjectManager();
        if (is_string($objectManager)) {
            $options->setObjectManager($container->get($objectManager));
        }

        return new ObjectRepository($options);
    }

    public function getOptionsClass(): string
    {
        return Authentication::class;
    }
}
