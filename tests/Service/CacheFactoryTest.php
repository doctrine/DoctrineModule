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

namespace DoctrineModuleTest\Service;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\PredisCache;
use DoctrineModule\Cache\ZendStorageCache;
use DoctrineModule\Service\CacheFactory;
use Predis\ClientInterface;
use Zend\Cache\Service\StorageCacheAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Test for {@see \DoctrineModule\Service\CacheFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \DoctrineModule\Service\CacheFactory::__invoke
     */
    public function testWillSetNamespace()
    {
        $factory        = new CacheFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                 'doctrine' => [
                     'cache' => [
                         'foo' => [
                             'namespace' => 'bar',
                         ],
                     ],
                 ],
            ]
        );

        /** @var $service ArrayCache */
        $service = $factory($serviceManager, ArrayCache::class);

        $this->assertInstanceOf(ArrayCache::class, $service);
        $this->assertSame('bar', $service->getNamespace());
    }

    /**
     * @covers \DoctrineModule\Service\CacheFactory::__invoke
     * @group 547
     */
    public function testCreateZendCache()
    {
        $factory        = new CacheFactory('phpunit');
        $serviceManager = new ServiceManager();
        $serviceManager->setAlias('config', 'Configuration');
        $serviceManager->setService(
            'Configuration',
            [
                'doctrine' => [
                    'cache' => [
                        'phpunit' => [
                            'class'     => ZendStorageCache::class,
                            'instance'  => 'my-zend-cache',
                            'namespace' => 'DoctrineModule',
                        ],
                    ],
                ],
                'caches' => [
                    'my-zend-cache' => [
                        'adapter' => [
                            'name' => 'blackhole',
                        ],
                    ],
                ],
            ]
        );
        $serviceManager->addAbstractFactory(StorageCacheAbstractServiceFactory::class);

        $cache = $factory($serviceManager, ZendStorageCache::class);

        $this->assertInstanceOf(ZendStorageCache::class, $cache);
    }

    public function testCreatePredisCache()
    {
        $factory        = new CacheFactory('predis');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            [
                'doctrine' => [
                    'cache' => [
                        'predis' => [
                            'class'     => PredisCache::class,
                            'instance'  => 'my_predis_alias',
                            'namespace' => 'DoctrineModule',
                        ],
                    ],
                ],
            ]
        );
        $serviceManager->setService(
            'my_predis_alias',
            $this->getMockBuilder(ClientInterface::class)->getMock()
        );
        $cache = $factory($serviceManager, PredisCache::class);

        $this->assertInstanceOf(PredisCache::class, $cache);
    }
}
