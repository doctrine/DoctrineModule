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
use Symfony\Component\Console\Input\StringInput;

use function class_exists;
use function interface_exists;
use function sprintf;
use function trigger_error;

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
    public function getConsoleUsage($console)
    {
        if (! interface_exists(Console::class)) {
            trigger_error(sprintf(
                'Using %s requires the package laminas/laminas-console, which is currently not installed.',
                __METHOD__
            ));

            return '';
        }

        $cli    = $this->serviceManager->get('doctrine.cli');
        $output = new PropertyOutput();

        $cli->run(new StringInput('list'), $output);

        return $output->getMessage();
    }
}
