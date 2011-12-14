<?php
namespace SpiffyDoctrine;

use Zend\EventManager\Event,
    Zend\Module\Consumer\AutoloaderProvider,
    Zend\Module\Manager;

class Module implements AutoloaderProvider
{
    public function getAutoloaderConfig()
    {
        /**
         * Autoloading is tricky due to multiple instances of Doctrine potentially being available.
         * I assume most people will be using Doctrine ORM so I will be loading from there first,
         * followed by MongoDB. I do this by checking that a file exists in the vendor/doctrine-orm
         * file and if so, load all shared Doctrine classes from there.
         */
        $ormdir = __DIR__ . '/vendor/doctrine-orm/lib/vendor/doctrine-common/lib/Doctrine/Common';
        
        $namespaces = array();
        if (file_exists($ormdir)) {
            $namespaces['Doctrine\Common'] = __DIR__ . '/vendor/doctrine-orm/lib/vendor/doctrine-common/lib/Doctrine/Common';
        } else {
            $namespaces['Doctrine\Common'] = __DIR__ . '/vendor/doctrine-odm/lib/vendor/doctrine-common/lib/Doctrine/Common';
        }
        
        // ORM
        $namespaces['Doctrine\DBAL'] = __DIR__ . '/vendor/doctrine-orm/lib/vendor/doctrine-dbal/lib/Doctrine/DBAL';
        $namespaces['Doctrine\ORM']  = __DIR__ . '/vendor/doctrine-orm/lib/Doctrine/ORM';
        
        // MongoDB
        $namespaces['Doctrine\MongoDB']     = __DIR__ . '/vendor/doctrine-odm/lib/vendor/doctrine-mongodb/lib/Doctrine/MongoDB';
        $namespaces['Doctrine\ODM\MongoDB'] = __DIR__ . '/vendor/doctrine-odm/lib/Doctrine/ODM/MongoDB';
        
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => $namespaces
            ),
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
        );
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
