<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule\Service;

use Doctrine\Common\Cache\CacheProvider;
use RuntimeException;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
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
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $options \DoctrineModule\Options\Cache */
        $options = $this->getOptions($serviceLocator, 'cache');
        $class   = $options->getClass();

        if (!$class) {
            throw new RuntimeException('Cache must have a class name to instantiate');
        }

        $instance = $options->getInstance();

        if (is_string($instance) && $serviceLocator->has($instance)) {
            $instance = $serviceLocator->get($instance);
        }

        switch ($this->name) {
            case 'filesystem':
                $cache = new $class($options->getDirectory());
                break;

            case 'predis':
                $cache = new $class($instance);
                break;

            default:
                $cache = new $class;
        }

        if ($cache instanceof MemcacheCache) {
            /* @var $cache MemcacheCache */
            $cache->setMemcache($instance);
        } elseif ($cache instanceof MemcachedCache) {
            /* @var $cache MemcachedCache */
            $cache->setMemcached($instance);
        } elseif ($cache instanceof RedisCache) {
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
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Cache';
    }
}
