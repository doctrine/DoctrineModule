<?php

namespace DoctrineModule\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;

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
     * {@inheritDoc}
     */
    protected function doSave($id, $data, $lifeTime = false)
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
