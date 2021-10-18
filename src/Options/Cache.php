<?php

declare(strict_types=1);

namespace DoctrineModule\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * Cache options
 *
 * @link    http://www.doctrine-project.org/
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
     * @var string|null
     */
    protected $instance = null;

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setInstance(string $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = (string) $namespace;

        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setDirectory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
