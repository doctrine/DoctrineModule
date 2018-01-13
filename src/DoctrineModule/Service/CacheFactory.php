<?php

namespace DoctrineModule\Service;

use Doctrine\Common\Cache\CacheProvider;
use Interop\Container\ContainerInterface;
use RuntimeException;
use Doctrine\Common\Cache;
use DoctrineModule\Cache\ZendStorageCache;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Cache ServiceManager factory
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class CacheFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     *
     * @return \Doctrine\Common\Cache\Cache
     *
     * @throws RuntimeException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $options \DoctrineModule\Options\Cache */
        $options = $this->getOptions($container, 'cache');
        $class   = $options->getClass();

        if (! $class) {
            throw new RuntimeException('Cache must have a class name to instantiate');
        }

        $instance = $options->getInstance();

        if (is_string($instance) && $container->has($instance)) {
            $instance = $container->get($instance);
        }

        if ($container->has($class)) {
            $cache = $container->get($class);
        } else {
            switch ($class) {
                case Cache\FilesystemCache::class:
                    $cache = new $class($options->getDirectory());
                    break;

                case ZendStorageCache::class:
                case Cache\PredisCache::class:
                    $cache = new $class($instance);
                    break;

                default:
                    $cache = new $class;
                    break;
            }
        }

        if ($cache instanceof Cache\MemcacheCache) {
            /* @var $cache MemcacheCache */
            $cache->setMemcache($instance);
        } elseif ($cache instanceof Cache\MemcachedCache) {
            /* @var $cache MemcachedCache */
            $cache->setMemcached($instance);
        } elseif ($cache instanceof Cache\RedisCache) {
            /* @var $cache RedisCache */
            $cache->setRedis($instance);
        }

        if ($cache instanceof CacheProvider && ($namespace = $options->getNamespace())) {
            $cache->setNamespace($namespace);
        }

        return $cache;
    }

    /**
     * {@inheritDoc}
     *
     * @return \Doctrine\Common\Cache\Cache
     *
     * @throws RuntimeException
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, \Doctrine\Common\Cache\Cache::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Cache';
    }
}
