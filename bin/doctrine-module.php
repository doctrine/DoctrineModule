<?php
ini_set('display_errors', true);
chdir(__DIR__);

$previousDir = '.';
while (!file_exists('config/application.config.php')) {
    $dir = dirname(getcwd());
    if ($previousDir === $dir) {
        throw new RuntimeException(
            'Unable to locate "config/application.config.php":'
                . ' is DoctrineModule in a subdir of your application skeleton?'
        );
    }
    $previousDir = $dir;
    chdir($dir);
}

require_once (getenv('ZF2_PATH') ?: 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';

use Zend\Loader\AutoloaderFactory,
Zend\ServiceManager\ServiceManager,
Zend\Mvc\Service\ServiceManagerConfiguration;

// setup autoloader
AutoloaderFactory::factory();

// get application stack configuration
$configuration = include 'config/application.config.php';

// setup service manager
$serviceManager = new ServiceManager(new ServiceManagerConfiguration($configuration['service_manager']));
$serviceManager->setService('ApplicationConfiguration', $configuration);
$serviceManager->get('ModuleManager')->loadModules();
$application = $serviceManager->get('Application');

$serviceManager
    ->get('Di')
    ->get('doctrine_cli')
    ->run();
