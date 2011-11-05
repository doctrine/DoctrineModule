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
$config = $config['di']['instance']['doctrine']['parameters'];
$config['conn'] = array(
    'driver' => 'pdo_sqlite',
    'memory' => true
);
$config['config']['metadata-driver-impl'] = array(
    'test-annotation-driver' => array(
        'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
        'namespace' => 'SpiffyDoctrineTest\Assets\Entity',
        'paths' => array(__DIR__ . '/SpiffyDoctrineTest/Assets/Entity'),
        'cache-class' => 'Doctrine\Common\Cache\ArrayCache'
    ),
);

\SpiffyDoctrineTest\Framework\TestCase::$config = $config;