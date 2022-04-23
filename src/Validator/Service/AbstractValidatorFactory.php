<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use DoctrineModule\Validator\Service\Exception\ServiceCreationException;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\ArrayUtils;

use function is_string;
use function sprintf;

/**
 * Factory for creating NoObjectExists instances
 *
 * @link http://www.doctrine-project.org/
 */
// phpcs:disable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming
abstract class AbstractValidatorFactory implements FactoryInterface
{
// phpcs:enable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming
    public const DEFAULT_OBJECTMANAGER_KEY = 'doctrine.entitymanager.orm_default';

    /** @var mixed[] */
    protected $creationOptions = [];

    /** @var string $validatorClass */
    protected $validatorClass;

    /**
     * @param mixed[] $options
     *
     * @throws ServiceCreationException
     */
    protected function getRepository(ContainerInterface $container, ?array $options = null): ObjectRepository
    {
        if (empty($options['target_class'])) {
            throw new ServiceCreationException(sprintf(
                "Option 'target_class' is missing when creating validator %s",
                self::class
            ));
        }

        $objectManager   = $this->getObjectManager($container, $options);
        $targetClassName = $options['target_class'];

        return $objectManager->getRepository($targetClassName);
    }

    /**
     * @param mixed[] $options
     */
    protected function getObjectManager(ContainerInterface $container, ?array $options = null): ObjectManager
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

    /**
     * Helper method for Laminas compatiblity.
     *
     * In Laminas v2 the plugin manager instance if passed to `createService`
     * instead of the global service manager instance (as in Laminas v3).
     *
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     */
    protected function container(ContainerInterface $container): ContainerInterface
    {
        return $container;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, $this->validatorClass, $this->creationOptions);
    }

    /**
     * @deprecated 4.2.0 With laminas-servicemanager v3 this method is obsolete and will be removed in 5.0.0.
     *
     * @param mixed[] $options
     */
    public function setCreationOptions(array $options): void
    {
        $this->creationOptions = $options;
    }
}
