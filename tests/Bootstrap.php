<?php
require_once __DIR__ . '/../autoload_register.php';

$rootPath  = realpath(dirname(__DIR__));
$testsPath = "$rootPath/tests";

if (is_readable($testsPath . '/TestConfiguration.php')) {
    require_once $testsPath . '/TestConfiguration.php';
} else {
    require_once $testsPath . '/TestConfiguration.php.dist';
}

$path = array(
    $testsPath,
    ZEND_FRAMEWORK_PATH,
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $path));

require_once 'Zend/Loader/AutoloaderFactory.php';
\Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => array()));

$moduleLoader = new \Zend\Loader\ModuleAutoloader(array(
    realpath(__DIR__ . '/../..'),
    realpath(__DIR__ . '/../../..')
));
$moduleLoader->register();

$moduleManager = new \Zend\Module\Manager(array('SpiffyDoctrine'));
$moduleManager->loadModule('SpiffyDoctrine');

$config = $moduleManager->getMergedConfig()->toArray();

// setup sqlite
$config['di']['instance']['doctrine_connection']['parameters']['params'] = array(
	'driver' => 'pdo_sqlite',
	'memory' => true
);

// setup the driver
$config['di']['instance']['doctrine_driver_chain']['parameters']['drivers']['doctrine_test_driver'] = array(
	'class' 	=> 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
	'namespace' => 'SpiffyDoctrineTest\Assets\Entity',
	'paths'     => array( __DIR__ . '/SpiffyDoctrineTests/src/SpiffyDoctrineTests/Assets/Entity')
);

$di = new \Zend\Di\Di;
$di->instanceManager()->addTypePreference('Zend\Di\Locator', $di);

$config = new \Zend\Di\Configuration($config['di']);
$config->configure($di);

\SpiffyDoctrineTest\Framework\TestCase::$locator = $di;