<?php

namespace DoctrineModule\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * Cache options
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class Cache extends AbstractOptions
{
    /**
     * Class used to instantiate the cache.
     *
     * @var string
     */
    protected $class = 'Doctrine\Common\Cache\ArrayCache';

    /**
     * Namespace to prefix all cache ids with.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Directory for file-based caching
     *
     * @var string
     */
    protected $directory;

    /**
     * Key to use for fetching the memcache, memcached, or redis instance from
     * the service locator. Used only with Memcache. Memcached, and Redis.
     *
     * @var string
     */
    protected $instance = null;

    /**
     * @param  string $class
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param  string $instance
     * @return self
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param  string $namespace
     * @return self
     */
    public function setNamespace($namespace)
    {
        $this->namespace = (string) $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param  string $directory
     * @return self
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}
