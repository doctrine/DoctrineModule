<?php

namespace DoctrineModule\Options;

use Zend\Stdlib\Options;

class Connection extends Options
{
    /**
     * Set the configuration key for the Configuration. Configuration key
     * is assembled as "doctrine.configuration.{key}" and pulled from
     * service locator.
     *
     * @var string
     */
    protected $configuration = 'orm_default';

    /**
     * Set the eventmanager key for the EventManager. EventManager key
     * is assembled as "doctrine.eventmanager.{key}" and pulled from
     * service locator.
     *
     * @var string
     */
    protected $eventmanager = 'orm_default';

    /**
     * Set the PDO instance, if any, to use. If a string is set
     * then the alias is pulled from the service locator.
     *
     * @var null|string|\PDO
     */
    protected $pdo = null;

    /**
     * Setting the driver is deprecated. You should set the
     * driver class directly instead.
     *
     * @var string
     */
    protected $driverClass = 'Doctrine\DBAL\Driver\PDOMySql\Driver';

    /**
     * Set the wrapper class for the driver. In general, this shouldn't
     * need to be changed.
     *
     * @var string|null
     */
    protected $wrapperClass = null;

    /**
     * Driver specific connection parameters.
     *
     * @var array
     */
    protected $params = array();

    /**
     * @param string $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return "doctrine.configuration.{$this->configuration}";
    }

    /**
     * @param string $eventmanager
     */
    public function setEventmanager($eventmanager)
    {
        $this->eventmanager = $eventmanager;
    }

    /**
     * @return string
     */
    public function getEventmanager()
    {
        return "doctrine.eventmanager.{$this->eventmanager}";
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param null|string $driverClass
     */
    public function setDriverClass($driverClass)
    {
        $this->driverClass = $driverClass;
    }

    /**
     * @return null|string
     */
    public function getDriverClass()
    {
        return $this->driverClass;
    }

    /**
     * @param null|\PDO|string $pdo
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return null|\PDO|string
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param string $wrapperClass
     */
    public function setWrapperClass($wrapperClass)
    {
        $this->wrapperClass = $wrapperClass;
    }

    /**
     * @return string
     */
    public function getWrapperClass()
    {
        return $this->wrapperClass;
    }
}