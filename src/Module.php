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

    /**
     * {@inheritDoc}
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        AnnotationRegistry::registerLoader(
            static function ($className) {
                return class_exists($className);
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        assert($event->getTarget() instanceof Application);
        $this->serviceManager = $event->getTarget()->getServiceManager();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
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
