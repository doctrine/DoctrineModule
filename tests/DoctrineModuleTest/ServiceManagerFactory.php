<?php

namespace DoctrineModuleTest;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Base test case to be used when a service manager instance is required
 */
class ServiceManagerFactory
{
    /**
     * @return array
     */
    public static function getConfiguration()
    {
        return include __DIR__ . '/../TestConfiguration.php';
    }

    /**
     * Retrieves a new ServiceManager instance
     *
     * @param  array|null     $configuration
     * @return ServiceManager
     */
    public static function getServiceManager(array $configuration = null)
    {
        $configuration        = $configuration ?: static::getConfiguration();
        $serviceManager       = new ServiceManager();
        $serviceManagerConfig = new ServiceManagerConfig($configuration['service_manager'] ?? []);
        $serviceManagerConfig->configureServiceManager($serviceManager);

        $serviceManager->setService('ApplicationConfig', $configuration);
        if (! $serviceManager->has('ServiceListener')) {
            $serviceManager->setFactory('ServiceListener', 'Zend\Mvc\Service\ServiceListenerFactory');
        }

        /* @var $moduleManager \Zend\ModuleManager\ModuleManagerInterface */
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();

        return $serviceManager;
    }
}
