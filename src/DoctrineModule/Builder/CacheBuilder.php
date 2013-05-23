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

namespace DoctrineModule\Builder;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use DoctrineModule\Exception;
use DoctrineModule\Options\CacheOptions;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Cache ServiceManager factory
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class CacheBuilder implements AbstractBuilderInterface, ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * {@inheritDoc}
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * {@inheritDoc}
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritDoc}
     *
     * @return \Doctrine\Common\Cache\Cache
     *
     * @throws RuntimeException
     */
    public function build($options)
    {
        if (is_array($options) || $options instanceof \Traversable) {
            $options = new CacheOptions($options);
        } elseif (! $options instanceof CacheOptions) {
            throw new Exception\InvalidArgumentException();
        }

        $class   = $options->getClass();

        if (!$class) {
            throw new RuntimeException('Cache must have a class name to instantiate');
        }

        if ($options->getDirectory() !== null) {
            $cache = new $class($options->getDirectory());
        } else {
            $cache = new $class;
        }

        $instance = $options->getInstance();
        if (is_string($instance) && $this->serviceLocator->has($instance)) {
            $instance = $this->serviceLocator->get($instance);
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

        ;

        if ($cache instanceof CacheProvider && ($namespace = $options->getNamespace())) {
            $cache->setNamespace($namespace);
        }

        return $cache;
    }
}
