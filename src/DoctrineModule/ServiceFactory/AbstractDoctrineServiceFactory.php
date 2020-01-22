<?php

namespace DoctrineModule\ServiceFactory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract service factory capable of instantiating services whose names match the
 * pattern <code>doctrine.$serviceType.$serviceName</doctrine>
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class AbstractDoctrineServiceFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return false !== $this->getFactoryMapping($container, $requestedName);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mappings = $this->getFactoryMapping($container, $requestedName);

        if (! $mappings) {
            throw new ServiceNotFoundException();
        }

        $factoryClass = $mappings['factoryClass'];
        /* @var $factory \DoctrineModule\Service\AbstractFactory */
        $factory = new $factoryClass($mappings['serviceName']);

        return $factory->createService($container);
    }

    /**
     * {@inheritDoc}
     * @deprecated
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this->canCreate($container, $requestedName);
    }

    /**
     * {@inheritDoc}
     * @deprecated
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this($serviceLocator, $requestedName);
    }

    /**
     * @param ContainerInterface $serviceLocator
     * @param string             $name
     *
     * @return array|bool
     */
    private function getFactoryMapping(ContainerInterface $serviceLocator, $name)
    {
        $matches = [];

        if (! preg_match(
            '/^doctrine\.((?<mappingType>orm|odm)\.|)(?<serviceType>[a-z0-9_]+)\.(?<serviceName>[a-z0-9_]+)$/',
            $name,
            $matches
        )) {
            return false;
        }

        $config      = $serviceLocator->get('config');
        $mappingType = $matches['mappingType'];
        $serviceType = $matches['serviceType'];
        $serviceName = $matches['serviceName'];

        if ($mappingType == '') {
            if (! isset($config['doctrine_factories'][$serviceType]) ||
                 ! isset($config['doctrine'][$serviceType][$serviceName])
            ) {
                return false;
            }

            return [
                'serviceType'  => $serviceType,
                'serviceName'  => $serviceName,
                'factoryClass' => $config['doctrine_factories'][$serviceType],
            ];
        } else {
            if (! isset($config['doctrine_factories'][$mappingType]) ||
                 ! isset($config['doctrine_factories'][$mappingType][$serviceType]) ||
                 ! isset($config['doctrine'][$mappingType][$serviceType][$serviceName])
            ) {
                return false;
            }
            return [
                'serviceType'  => $serviceType,
                'serviceName'  => $serviceName,
                'factoryClass' => $config['doctrine_factories'][$mappingType][$serviceType],
            ];
        }
    }
}
