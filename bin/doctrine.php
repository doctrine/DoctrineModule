<?php
use Zend\Loader\AutoloaderFactory,
    Zend\Config\Config,
    Zend\Loader\ModuleAutoloader,
    Zend\Module\Manager as ModuleManager,
    Zend\Module\ManagerOptions,
    Zend\Mvc\Bootstrap,
    Zend\Mvc\Application,
    Doctrine\ORM\Tools\Console\ConsoleRunner,
    Symfony\Component\Console\Helper\HelperSet,
    Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

ini_set('display_errors', true);
error_reporting(-1);

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '/../../../library'), // just for git submodule
    realpath(__DIR__ . '/../../../library/ZendFramework/library'), // just for git submodule
    get_include_path(),
)));

require_once 'Zend/Loader/AutoloaderFactory.php';
AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => array()));

$appConfig = new Config(include __DIR__ . '/../../../configs/application.config.php');

$moduleLoader = new ModuleAutoloader($appConfig['module_paths']);
$moduleLoader->register();

$moduleManager = new ModuleManager(
    $appConfig['modules'],
    new ManagerOptions($appConfig['module_manager_options'])
);

$bootstrap      = new Bootstrap($moduleManager);
$application    = new Application();
$bootstrap->bootstrap($application);
$locator = $application->getLocator();

ConsoleRunner::run(new HelperSet(array(
    'em' => new EntityManagerHelper($locator->get('spiffy-em'))
)));
