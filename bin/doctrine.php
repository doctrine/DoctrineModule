<?php

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
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => array()));

$appConfig = new Zend\Config\Config(include __DIR__ . '/../../../configs/application.config.php');

$moduleLoader = new Zend\Loader\ModuleAutoloader($appConfig['module_paths']);
$moduleLoader->register();

$moduleManager = new Zend\Module\Manager(
    array('SpiffyDoctrine', 'Application'),
    new Zend\Module\ManagerOptions($appConfig['module_manager_options'])
);

\Doctrine\ORM\Tools\Console\ConsoleRunner::run(
    new \Symfony\Component\Console\Helper\HelperSet()
);
