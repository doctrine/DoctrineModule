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

namespace DoctrineModule;

use DoctrineModule\Service\AuthenticationFactory;
use DoctrineModule\Service\CacheFactory;
use DoctrineModule\Service\ZendStorageCacheFactory;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Base module for integration of Doctrine projects with ZF2 applications
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class Module implements ServiceProviderInterface
{
    /**
     * Retrieves configuration that can be consumed by Zend\Loader\AutoloaderFactory
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Expected to return \Zend\ServiceManager\Configuration object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'doctrine.cli'                    => 'DoctrineModule\Service\CliFactory',
                'doctrine.cache.apc'              => new CacheFactory('apc'),
                'doctrine.cache.array'            => new CacheFactory('array'),
                'doctrine.cache.memcache'         => new CacheFactory('memcache'),
                'doctrine.cache.memcached'        => new CacheFactory('memcached'),
                'doctrine.cache.redis'            => new CacheFactory('redis'),
                'doctrine.cache.wincache'         => new CacheFactory('wincache'),
                'doctrine.cache.xcache'           => new CacheFactory('xcache'),
                'doctrine.cache.zenddata'         => new CacheFactory('zenddata'),
                'doctrine.cache.zendcachestorage' => new ZendStorageCacheFactory('zendcachestorage')
            )
        );
    }
}
