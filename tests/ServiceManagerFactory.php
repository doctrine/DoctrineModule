<?php

declare(strict_types=1);

namespace DoctrineModuleTest;

use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\Service\ServiceListenerFactory;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

use function assert;

/**
 * Base test case to be used when a service manager instance is required
 */
class ServiceManagerFactory
{
    /**
     * @return mixed[]
     */
    public static function getConfiguration(): array
    {
        return include __DIR__ . '/TestConfiguration.php';
    }

    /**
     * Retrieves a new ServiceManager instance
     *
     * @param mixed[]|null $configuration
     */
    public static function getServiceManager(?array $configuration = null): ServiceManager
    {
        $configuration        = $configuration ?: static::getConfiguration();
        $serviceManager       = new ServiceManager();
        $serviceManagerConfig = new ServiceManagerConfig($configuration['service_manager'] ?? []);
        $serviceManagerConfig->configureServiceManager($serviceManager);

        $serviceManager->setService('ApplicationConfig', $configuration);
        if (! $serviceManager->has('ServiceListener')) {
            $serviceManager->setFactory('ServiceListener', ServiceListenerFactory::class);
        }

        $moduleManager = $serviceManager->get('ModuleManager');
        assert($moduleManager instanceof ModuleManagerInterface);
        $moduleManager->loadModules();

        return $serviceManager;
    }
}
