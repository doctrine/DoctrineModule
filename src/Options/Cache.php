<?php

declare(strict_types=1);

namespace DoctrineModule\Options;

use DoctrineModule\Cache\LaminasStorageCache;
use Laminas\Stdlib\AbstractOptions;

/**
 * Cache options
 *
 * @template-extends AbstractOptions<mixed>
 */
final class Cache extends AbstractOptions
{
    /**
     * Class used to instantiate the cache.
     */
    protected string $class = LaminasStorageCache::class;

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
    protected string|null $instance = null;

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

    public function getInstance(): string|null
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
