<?php

declare(strict_types=1);

namespace DoctrineModule\Service\Authentication;

use DoctrineModule\Authentication\Storage\ObjectRepository;
use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function is_string;

/**
 * Factory to create authentication storage object.
 *
 * @link    http://www.doctrine-project.org/
 */
class StorageFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = $this->getOptions($container, 'authentication');

        $objectManager = $options->getObjectManager();
        if (is_string($objectManager)) {
            $options->setObjectManager($container->get($objectManager));
        }

        $storage = $options->getStorage();
        if (is_string($storage)) {
            $sessionContainer = $options->getSessionContainer();
            $sessionMember = $options->getSessionMember();
            if ($sessionContainer || $sessionMember) {
                $storage = new Session(
                    $options->getSessionContainer(),
                    $options->getSessionMember()
                );
            } else {
                $storage = $container->get($storage);
            }
            $options->setStorage($storage);
        }

        return new ObjectRepository($options);
    }

    /**
     * {@inheritDoc}
     *
     * @return ObjectRepository
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ObjectRepository::class);
    }

    public function getOptionsClass(): string
    {
        return 'DoctrineModule\Options\Authentication';
    }
}
