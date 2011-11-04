<?php
require_once __DIR__ . '/../autoload_register.php';

$rootPath  = realpath(dirname(__DIR__));
$testsPath = "$rootPath/tests";

/*
 * Load the default configuration and overwrite with user configuration if
 * it exists.
 */
require_once $testsPath . '/TestConfiguration.php.dist';
if (is_readable($testsPath . '/TestConfiguration.php')) {
    require_once $testsPath . '/TestConfiguration.php';
}

/**
 * Register include path with tests and Zend Framework.
 */
$path = array(
    TESTS_PATH,
    ZEND_FRAMEWORK_PATH,
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $path));

require_once 'Zend/Loader/AutoloaderFactory.php';
\Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => array()));

$moduleLoader = new \Zend\Loader\ModuleAutoloader(array(
    __DIR__ . '/../modules',
    __DIR__ . '/../modules/vendor'
));
$moduleLoader->register();

$moduleManager = new \Zend\Module\Manager(array(MODULE_NAME));
$moduleManager->loadModule(MODULE_NAME);

// * SPIFFYDOCTRINE SPECIFIC
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
    )
);

\SpiffyDoctrineTest\Framework\TestCase::$config = $config;