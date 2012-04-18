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

require_once (getenv('ZF2_PATH') ? : 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory();

$appConfig = include 'config/application.config.php';

$moduleManager = new Zend\Module\Manager($appConfig['modules']);
$listenerOptions = new Zend\Module\Listener\ListenerOptions($appConfig['module_listener_options']);
$defaultListeners = new Zend\Module\Listener\DefaultListenerAggregate($listenerOptions);

$defaultListeners->getConfigListener()->addConfigGlobPath('config/autoload/{,*.}{global,local}.config.php');
$moduleManager->events()->attachAggregate($defaultListeners);
$moduleManager->loadModules();

// Create application, bootstrap, and run
$bootstrap = new Zend\Mvc\Bootstrap($defaultListeners->getConfigListener()->getMergedConfig());
$application = new Zend\Mvc\Application();
$bootstrap->bootstrap($application);

/* @var $cli \Symfony\Component\Console\Application */
$cli = $application->getLocator()->get('doctrine_cli');

if (class_exists('Doctrine\ODM\MongoDB\Version')) {
    $cli->getHelperSet()->set(
        new \Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper($locator->get('doctrine_mongo')),
        'dm'
    );
    $cli->addCommands(array(
        new \Doctrine\ODM\MongoDB\Tools\Console\Command\QueryCommand(),
        new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateDocumentsCommand(),
        new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateRepositoriesCommand(),
        new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateProxiesCommand(),
        new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateHydratorsCommand(),
        new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\CreateCommand(),
        new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\DropCommand(),
    ));
}

$cli->run();
