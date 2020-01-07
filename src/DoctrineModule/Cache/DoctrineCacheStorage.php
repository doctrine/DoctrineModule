<?php

namespace DoctrineModule\Cache;

use Doctrine\Common\Cache\Cache;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;

/**
 * Bridge class that allows usage of a Doctrine Cache Storage as a Zend Cache Storage
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class DoctrineCacheStorage extends AbstractAdapter
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * {@inheritDoc}
     * @param Cache $cache
     */
    public function __construct($options, Cache $cache)
    {
        parent::__construct($options);

        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $key     = $this->getOptions()->getNamespace() . $normalizedKey;
        $fetched = $this->cache->fetch($key);
        $success = ($fetched === false ? false : true);

        if ($success) {
            $casToken = $fetched;

            return $fetched;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $key = $this->getOptions()->getNamespace() . $normalizedKey;
        $ttl = $this->getOptions()->getTtl();

        return $this->cache->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $key = $this->getOptions()->getNamespace() . $normalizedKey;
        if (! $this->cache->contains($key)) {
            return false;
        }

        return $this->cache->delete($key);
    }
}
