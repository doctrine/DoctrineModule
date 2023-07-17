<?php

declare(strict_types=1);

namespace DoctrineModule;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Base module for integration of Doctrine projects with Laminas applications
 */
final class Module implements ConfigProviderInterface
{
    /** @return array<string, mixed> */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'caches' => $provider->getCachesConfig(),
            'doctrine' => $provider->getDoctrineConfig(),
            'doctrine_factories' => $provider->getDoctrineFactoryConfig(),
            'service_manager' => $provider->getDependencyConfig(),
            'validators' => $provider->getValidatorConfig(),
        ];
    }
}
