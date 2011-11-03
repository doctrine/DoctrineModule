<?php
namespace SpiffyDoctrine\DBAL;

use Doctrine\DBAL\DriverManager as DBALManager,
    Doctrine\Common\EventManager,
    Doctrine\DBAL\DBALException,
    Doctrine\DBAL\Configuration,
    PDO;

/**
 * Simple extension of the DriverManager to allow construction via direct PDO instance
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class DriverManager 
{
    
    /**
     * Wraps around the standard DBAL DriverManager so that we can pass a
     * PDO instance to Doctrine. This is necessary because Zend\Di does not
     * support passing Di configured objects in an array.
     * 
     * @param Configuration $config
     * @param EventManager $eventManager
     * @param array $params
     * @return Doctrine\DBAL\Connection
     */
    public static function getConnection
    (
        array $params = array(),
        Configuration $config = null,
        EventManager $eventManager = null,
        $pdo = null
    ) {
        $params['pdo'] = $pdo;
        return DBALManager::getConnection($params, $config, $eventManager);
    }
    
}