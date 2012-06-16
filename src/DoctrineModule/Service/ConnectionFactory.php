<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Doctrine\DBAL\DriverManager;
use DoctrineModule\Service\AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConnectionFactory extends AbstractFactory
{
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var $options \DoctrineModule\Options\Connection */
        $options = $this->getOptions($sl, 'connection');
        $pdo     = $options->getPdo();

        if (is_string($pdo)) {
            $pdo = $sl->get($pdo);
        }

        $params = array(
            'driverClass'  => $options->getDriverClass(),
            'wrapperClass' => $options->getWrapperClass(),
            'pdo'          => $pdo,
        );
        $params = array_merge($params, $options->getParams());

        $configuration = $sl->get($options->getConfiguration());
        $eventManager  = $sl->get($options->getEventManager());

        return DriverManager::getConnection($params, $configuration, $eventManager);
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Connection';
    }
}