<?php

declare(strict_types=1);

namespace DoctrineModule;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function assert;
use function class_exists;

/**
 * Base module for integration of Doctrine projects with Laminas applications
 *
 * @link    http://www.doctrine-project.org/
 */
class Module implements ConfigProviderInterface, InitProviderInterface, BootstrapListenerInterface
{
    use GetConsoleUsage;

    /** @var ServiceLocatorInterface */
    private $serviceManager;

    public function init(ModuleManagerInterface $manager): void
    {
        AnnotationRegistry::registerLoader(
            static function ($className) {
                return class_exists($className);
            }
        );
    }

    public function onBootstrap(EventInterface $e): void
    {
        assert($e->getTarget() instanceof Application);
        $this->serviceManager = $e->getTarget()->getServiceManager();
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
            'controllers' => $provider->getControllerConfig(),
            'route_manager' => $provider->getRouteManagerConfig(),
            'console' => $provider->getConsoleConfig(),
            'validators' => $provider->getValidatorConfig(),
        ];
    }
}
