<?php

declare(strict_types=1);

namespace DoctrineModule;

use Doctrine\Common\Annotations\AnnotationRegistry;
use DoctrineModule\Component\Console\Output\PropertyOutput;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use function class_exists;

/**
 * Base module for integration of Doctrine projects with ZF2 applications
 *
 * @link    http://www.doctrine-project.org/
 */
class Module implements ConfigProviderInterface, InitProviderInterface, BootstrapListenerInterface
{
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

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(Console $console)
    {
        $cli = $this->serviceManager->get('doctrine.cli');
        $output = new PropertyOutput();

        $cli->run(new StringInput('list'), $output);

        return $output->getMessage();
    }
}
