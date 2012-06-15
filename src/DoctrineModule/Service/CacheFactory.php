<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Doctrine\Common\Cache;
use DoctrineModule\Service\AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheFactory extends AbstractFactory
{
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var $options \DoctrineModule\Options\Cache */
        $options = $this->getOptions($sl, 'cache');
        $class   = $options->getClass();

        if (!$class) {
            throw new RuntimeException('Cache must have a class name to instantiate');
        }

        $cache = new $class;

        if ($cache instanceof Cache\MemcacheCache) {
            $cache->setMemcache($options->getInstance());
        } else if ($cache instanceof Cache\MemcachedCache) {
            $cache->setMemcached($options->getInstance());
        } else if ($cache instanceof Cache\RedisCache) {
            $cache->setRedis($options->getInstance());
        }

        return $cache;
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Cache';
    }
}