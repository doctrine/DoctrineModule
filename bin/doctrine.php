<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner,
    Symfony\Component\Console\Helper\HelperSet,
    Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

ini_set('display_errors', true);
error_reporting(-1);

require_once dirname(__DIR__) . '/../../vendor/ZendFramework/library/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => array()));

$appConfig = include dirname(__DIR__) . '/../../config/application.config.php';

$moduleLoader = new Zend\Loader\ModuleAutoloader($appConfig['module_paths']);
$moduleLoader->register();

$moduleManager = new Zend\Module\Manager($appConfig['modules']);
$listenerOptions = new Zend\Module\Listener\ListenerOptions($appConfig['module_listener_options']);
$moduleManager->setDefaultListenerOptions($listenerOptions);
$moduleManager->getConfigListener()->addConfigGlobPath(dirname(__DIR__) . '/config/autoload/*.config.php');
$moduleManager->loadModules();

// Create application, bootstrap, and run
$bootstrap   = new Zend\Mvc\Bootstrap($moduleManager->getMergedConfig());
$application = new Zend\Mvc\Application;
$bootstrap->bootstrap($application);
$locator = $application->getLocator();

ConsoleRunner::run(new HelperSet(array(
    'em' => new EntityManagerHelper($locator->get('doctrine_em'))
)));
