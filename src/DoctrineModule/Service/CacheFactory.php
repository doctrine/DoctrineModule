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

use RuntimeException;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use DoctrineModule\Service\AbstractFactory;
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
     * @return \Doctrine\Common\Cache\Cache
     * @throws RuntimeException
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var $options \DoctrineModule\Options\Cache */
        $options = $this->getOptions($sl, 'cache');
        $class   = $options->getClass();

        if (!$class) {
            throw new RuntimeException('Cache must have a class name to instantiate');
        }

        if ($class === 'Doctrine\Common\Cache\FilesystemCache') {
            $cache = new $class($options->getDirectory());
        } else {
            $cache = new $class;
        }

        $instance = $options->getInstance();
        if (is_string($instance) && $sl->has($instance)) {
            $instance = $sl->get($instance);
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
