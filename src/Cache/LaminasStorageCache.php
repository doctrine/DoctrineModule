<?php

declare(strict_types=1);

namespace DoctrineModule\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Laminas\Cache\Storage\AvailableSpaceCapableInterface;
use Laminas\Cache\Storage\FlushableInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\Storage\TotalSpaceCapableInterface;

/**
 * Bridge class that allows usage of a Laminas Cache Storage as a Doctrine Cache
 */
class LaminasStorageCache extends CacheProvider
{
    public function __construct(protected StorageInterface $storage)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch($id)
    {
        $hit = $this->storage->getItem($id);

        return $hit ?? false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doContains($id)
    {
        return $this->storage->hasItem($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        // @todo check if lifetime can be set
        return $this->storage->setItem($id, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete($id)
    {
        return $this->storage->removeItem($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function doFlush()
    {
        if ($this->storage instanceof FlushableInterface) {
            $storage = $this->storage;

            return $storage->flush();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetStats()
    {
        $storage = $this->storage;

        return [
            Cache::STATS_HITS              => $this->storage->getMetadata(Cache::STATS_HITS),
            Cache::STATS_MISSES            => $this->storage->getMetadata(Cache::STATS_MISSES),
            Cache::STATS_UPTIME            => $this->storage->getMetadata(Cache::STATS_UPTIME),
            Cache::STATS_MEMORY_USAGE      => $storage instanceof TotalSpaceCapableInterface
                ? $storage->getTotalSpace()
                : null,
            Cache::STATS_MEMORY_AVAILIABLE => $storage instanceof AvailableSpaceCapableInterface
                ? $storage->getAvailableSpace()
                : null,
        ];
    }
}
