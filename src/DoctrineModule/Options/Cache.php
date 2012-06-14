<?php

namespace DoctrineModule\Options;

use Zend\Stdlib\Options;

class Cache extends Options
{
    /**
     * Class used to instantiate the cache.
     *
     * @var string
     */
    protected $class;

    /**
     * Namespace to prefix all cache ids with.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Key to use for fetching the memcache, memcached, or redis instance from
     * the service locator. Used only with Memcache. Memcached, and Redis.
     *
     * @var string
     */
    protected $instance;

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return string
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
}