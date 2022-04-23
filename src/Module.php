<?php

declare(strict_types=1);

namespace DoctrineModule;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\ModuleManagerInterface;

use function class_exists;

/**
 * Base module for integration of Doctrine projects with Laminas applications
 */
final class Module implements ConfigProviderInterface, InitProviderInterface
{
    public function init(ModuleManagerInterface $manager): void
    {
        AnnotationRegistry::registerLoader(
            static function ($className) {
                return class_exists($className);
            }
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'doctrine' => $provider->getDoctrineConfig(),
            'doctrine_factories' => $provider->getDoctrineFactoryConfig(),
            'service_manager' => $provider->getDependencyConfig(),
            'validators' => $provider->getValidatorConfig(),
        ];
    }
}
