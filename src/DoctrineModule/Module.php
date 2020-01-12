<?php

namespace DoctrineModule;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\EventManager\EventInterface;
use Laminas\Console\Adapter\AdapterInterface as Console;

use Symfony\Component\Console\Input\StringInput;
use DoctrineModule\Component\Console\Output\PropertyOutput;

/**
 * Base module for integration of Doctrine projects with ZF2 applications
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class Module implements ConfigProviderInterface, InitProviderInterface, BootstrapListenerInterface
{
    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
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
        /* @var $cli \Symfony\Component\Console\Application */
        $cli    = $this->serviceManager->get('doctrine.cli');
        $output = new PropertyOutput();

        $cli->run(new StringInput('list'), $output);

        return $output->getMessage();
    }
}
