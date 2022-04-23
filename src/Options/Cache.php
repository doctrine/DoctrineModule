<?php

declare(strict_types=1);

namespace DoctrineModule\Options;

use Doctrine\Common\Cache\ArrayCache;
use Laminas\Stdlib\AbstractOptions;

/**
 * Cache options
 */
final class Cache extends AbstractOptions
{
    /**
     * Class used to instantiate the cache.
     */
    protected string $class = ArrayCache::class;

    /**
     * Namespace to prefix all cache ids with.
     */
    protected string $namespace = '';

    /**
     * Directory for file-based caching
     */
    protected string $directory;

    /**
     * Key to use for fetching the memcache, memcached, or redis instance from
     * the service locator. Used only with Memcache. Memcached, and Redis.
     */
    protected ?string $instance = null;

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
        $this->namespace = $namespace;

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
