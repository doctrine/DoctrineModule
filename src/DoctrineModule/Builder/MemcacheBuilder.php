<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset;

use DoctrineModule\Exception;
use DoctrineModule\Options\MemcacheOptions;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MemcacheBuilder implements BuilderInterface
{

    /**
     * {@inheritDoc}
     */
    public function build($options)
    {
        if (is_array($options) || $options instanceof \Traversable) {
            $options = new MemcacheOptions($options);
        } elseif (! $options instanceof MemcacheOptions) {
            throw new Exception\InvalidArgumentException();
        }

        $memcache = new \Memcache;
        $memcache->connect($options->getHost(), $options->getPort());
        return $memcache;
    }
}
