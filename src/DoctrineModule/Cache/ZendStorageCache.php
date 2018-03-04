<?php

namespace DoctrineModule\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;

/**
 * Bridge class that allows usage of a Zend Cache Storage as a Doctrine Cache
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ZendStorageCache extends CacheProvider
{

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * it's a flag to check if there was a first call already
     *
     * @var bool
     */
    private static $firstCallWasSpawned = FALSE;

    /**
     * field for saving ttl from zend cache config
     *
     * @var null
     */
    private static $savedTtlFromConfig = null;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch($id)
    {
        $hit = $this->storage->getItem($id);

        return null === $hit ? false : $hit;
    }

    /**
     * {@inheritDoc}
     */
    protected function doContains($id)
    {
        return $this->storage->hasItem($id);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id         The cache id.
     * @param string $data       The cache entry/data.
     * @param int    $lifeTime   The lifetime. If != 0, sets a specific lifetime for this
     *                           cache entry (0 => infinite lifeTime).
     *                           If lifetime = -1, sets configured in cache config and
     *                           saved in static field ttl value.
     *
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if (!self::$firstCallWasSpawned) {
            self::$savedTtlFromConfig  = $this->storage->getOptions()->getTtl();
            self::$firstCallWasSpawned = TRUE;
        }

        if ($lifeTime && $lifeTime > 0) {
            //use ttl passed by parameter
            $this->storage->getOptions()->setTtl($lifeTime);
        } else if ($lifeTime === 0) {
            //use 0 ttl - not expired
            $this->storage->getOptions()->setTtl(0);
        } else if ($lifeTime === -1) {
            //use saved configured ttl
            if (self::$savedTtlFromConfig !== null) {
                $this->storage->getOptions()->setTtl(self::$savedTtlFromConfig);
            }
        }

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
            /* @var $storage FlushableInterface */
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
        /* @var $storage TotalSpaceCapableInterface */
        /* @var $storage AvailableSpaceCapableInterface */
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
