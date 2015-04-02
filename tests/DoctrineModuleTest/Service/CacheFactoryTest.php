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

use DoctrineModule\Service\CacheFactory;
use PHPUnit_Framework_TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * Test for {@see \DoctrineModule\Service\CacheFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class CacheFactoryTest extends BaseTestCase
{
    /**
     * @covers \DoctrineModule\Service\CacheFactory::createService
     */
    public function testWillSetNamespace()
    {
        $factory        = new CacheFactory('foo');
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            array(
                 'doctrine' => array(
                     'cache' => array(
                         'foo' => array(
                             'namespace' => 'bar',
                         ),
                     ),
                 ),
            )
        );

        /* @var $service \Doctrine\Common\Cache\ArrayCache */
        $service = $factory->createService($serviceManager);

        $this->assertInstanceOf('Doctrine\\Common\\Cache\\ArrayCache', $service);
        $this->assertSame('bar', $service->getNamespace());
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
                            'class'     => 'Doctrine\Common\Cache\PredisCache',
                            'instance'  => 'my_predis_alias',
                            'namespace' => 'DoctrineModule',
                        ],
                    ],
                ],
            ]
        )->setService(
            'my_predis_alias',
            $this->getMock('Predis\Client')
        );

        $cache = $factory->createService($serviceManager);

        $this->assertInstanceOf('Doctrine\Common\Cache\PredisCache', $cache);
    }
}
