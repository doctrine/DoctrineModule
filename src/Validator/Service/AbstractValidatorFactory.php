<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use BadMethodCallException;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use DoctrineModule\Validator\Service\Exception\ServiceCreationException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerInterface;

use function class_exists;
use function is_string;
use function sprintf;

/**
 * Base validator factory
 *
 * @internal
 */
abstract class AbstractValidatorFactory implements FactoryInterface
{
    public const DEFAULT_OBJECTMANAGER_KEY = 'doctrine.entitymanager.orm_default';

    /** @var mixed[] */
    protected array $creationOptions = [];

    protected string $validatorClass;

    /**
     * @param mixed[] $options
     *
     * @throws ServiceCreationException
     */
    protected function getRepository(ContainerInterface $container, array|null $options = null): ObjectRepository
    {
        if (empty($options['target_class'])) {
            throw new ServiceCreationException(sprintf(
                "Option 'target_class' is missing when creating validator %s",
                self::class,
            ));
        }

        $objectManager   = $this->getObjectManager($container, $options);
        $targetClassName = $options['target_class'];

        if (! class_exists($targetClassName)) {
            throw new BadMethodCallException(sprintf('Class %s could not be found.', $targetClassName));
        }

        return $objectManager->getRepository($targetClassName);
    }

    /** @param mixed[] $options */
    protected function getObjectManager(ContainerInterface $container, array|null $options = null): ObjectManager
    {
        $objectManager = $options['object_manager'] ?? self::DEFAULT_OBJECTMANAGER_KEY;

        if (is_string($objectManager)) {
            $objectManager = $container->get($objectManager);
        }

        return $objectManager;
    }

    /**
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    protected function getFields(array $options): array
    {
        if (isset($options['fields'])) {
            return (array) $options['fields'];
        }

        return [];
    }

    /**
     * Helper to merge options array passed to `__invoke`
     * together with the options array created based on the above
     * helper methods.
     *
     * @param mixed[] $previousOptions
     * @param mixed[] $newOptions
     *
     * @return mixed[]
     */
    protected function merge(array $previousOptions, array $newOptions): array
    {
        return ArrayUtils::merge($previousOptions, $newOptions, true);
    }
}
