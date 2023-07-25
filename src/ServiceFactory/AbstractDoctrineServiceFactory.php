<?php

declare(strict_types=1);

namespace DoctrineModule\ServiceFactory;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

use function preg_match;

/**
 * Abstract service factory capable of instantiating services whose names match the
 * pattern <code>doctrine.$serviceType.$serviceName</doctrine>
 */
final class AbstractDoctrineServiceFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->getFactoryMapping($container, $requestedName) !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array|null $options = null)
    {
        $mappings = $this->getFactoryMapping($container, $requestedName);

        if (! $mappings) {
            throw new ServiceNotFoundException();
        }

        $factoryClass = $mappings['factoryClass'];
        $factory      = new $factoryClass($mappings['serviceName']);

        return $factory->__invoke($container, $requestedName);
    }

    /** @return mixed[]|bool */
    private function getFactoryMapping(ContainerInterface $serviceLocator, string $name): array|bool
    {
        $matches = [];

        if (
            ! preg_match(
                '/^doctrine\.((?<mappingType>orm|odm)\.|)(?<serviceType>[a-z0-9_]+)\.(?<serviceName>[a-z0-9_]+)$/',
                $name,
                $matches,
            )
        ) {
            return false;
        }

        $config      = $serviceLocator->get('config');
        $mappingType = $matches['mappingType'];
        $serviceType = $matches['serviceType'];
        $serviceName = $matches['serviceName'];

        if ($mappingType === '') {
            if (
                ! isset($config['doctrine_factories'][$serviceType]) ||
                 ! isset($config['doctrine'][$serviceType][$serviceName])
            ) {
                return false;
            }

            return [
                'serviceType'  => $serviceType,
                'serviceName'  => $serviceName,
                'factoryClass' => $config['doctrine_factories'][$serviceType],
            ];
        }

        if (
            ! isset(
                $config['doctrine_factories'][$mappingType][$serviceType],
                $config['doctrine'][$mappingType][$serviceType][$serviceName],
            )
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
