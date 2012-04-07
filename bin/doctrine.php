<?php
ini_set('display_errors', true);
chdir(__DIR__);

$previousDir = '.';
while (!file_exists('config/application.config.php')) {
    $dir = dirname(getcwd());
    if($previousDir === $dir) {
        throw new RuntimeException(
            'Unable to locate "config/application.config.php":'
                . ' is DoctrineModule in a subdir of your application skeleton?'
        );
    }
    $previousDir = $dir;
    chdir($dir);
}
file_exists('vendor/.composer/autoload.php') && require_once 'vendor/.composer/autoload.php';

require_once (getenv('ZF2_PATH') ?: 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory();

$appConfig = include 'config/application.config.php';

$moduleManager    = new Zend\Module\Manager($appConfig['modules']);
$listenerOptions  = new Zend\Module\Listener\ListenerOptions($appConfig['module_listener_options']);
$defaultListeners = new Zend\Module\Listener\DefaultListenerAggregate($listenerOptions);

$defaultListeners->getConfigListener()->addConfigGlobPath("config/autoload/{,*.}{global,local}.config.php");
$moduleManager->events()->attachAggregate($defaultListeners);
$moduleManager->loadModules();

// Create application, bootstrap, and run
$bootstrap   = new Zend\Mvc\Bootstrap($defaultListeners->getConfigListener()->getMergedConfig());
$application = new Zend\Mvc\Application;
$bootstrap->bootstrap($application);
$locator = $application->getLocator();

$cli = new \Symfony\Component\Console\Application(
    'DoctrineModule Command Line Interface',
    \DoctrineModule\Version::VERSION
);
$cli->setCatchExceptions(true);

$helpers = array();
if (class_exists('Doctrine\ODM\MongoDB\Version')) {
    $helpers['dm'] = new \Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper($locator->get('doctrine_mongo'));
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

if (class_exists('Doctrine\ORM\Version')) {
    $helpers['em'] = new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($locator->get('doctrine_em'));
    $cli->addCommands(array(
        // DBAL Commands
        new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
        new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

        // ORM Commands
        new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
        new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
        new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
        new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
        new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
        new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
        new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
        new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
        new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
        new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
        new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
        new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
        new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
        new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),
        new \Doctrine\ORM\Tools\Console\Command\InfoCommand()
    ));
}

$helperSet = isset($helperSet) ? $helperSet : new \Symfony\Component\Console\Helper\HelperSet();
foreach ($helpers as $name => $helper) {
    $helperSet->set($helper, $name);
}
$cli->setHelperSet($helperSet);

$cli->run();
