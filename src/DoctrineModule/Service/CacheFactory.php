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
            /* @var $cache \Doctrine\Common\Cache\MemcacheCache */
            $cache->setMemcache($options->getInstance());
        } else if ($cache instanceof Cache\MemcachedCache) {
            /* @var $cache \Doctrine\Common\Cache\MemcachedCache */
            $cache->setMemcached($options->getInstance());
        } else if ($cache instanceof Cache\RedisCache) {
            /* @var $cache \Doctrine\Common\Cache\RedisCache */
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