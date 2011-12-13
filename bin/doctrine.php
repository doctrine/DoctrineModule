<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner,
    Symfony\Component\Console\Helper\HelperSet,
    Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

ini_set('display_errors', true);
error_reporting(-1);

chdir(dirname(__DIR__));
require_once dirname(__DIR__) . '/../../vendor/ZendFramework/library/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => array()));

$appConfig = include '../../config/application.config.php';

$moduleManager    = new Zend\Module\Manager($appConfig['modules']);
$listenerOptions  = new Zend\Module\Listener\ListenerOptions($appConfig['module_listener_options']);
$defaultListeners = new Zend\Module\Listener\DefaultListenerAggregate($listenerOptions);

$defaultListeners->getConfigListener()->addConfigGlobPath('config/autoload/*.{global,local}.config.php');
$moduleManager->events()->attachAggregate($defaultListeners);
$moduleManager->loadModules();

// Create application, bootstrap, and run
$bootstrap   = new Zend\Mvc\Bootstrap($defaultListeners->getConfigListener()->getMergedConfig());
$application = new Zend\Mvc\Application;
$bootstrap->bootstrap($application);
$locator = $application->getLocator();

ConsoleRunner::run(new HelperSet(array(
    'em' => new EntityManagerHelper($locator->get('doctrine_em'))
)));