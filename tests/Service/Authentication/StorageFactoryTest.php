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

namespace DoctrineModuleTest\Service\Authentication;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Authentication\Storage\ObjectRepository;
use DoctrineModule\Service\Authentication\StorageFactory;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\Storage\StorageInterface;
use Zend\ServiceManager\ServiceManager;

class StorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWillInstantiateFromFQCN()
    {
        $name    = 'testFactory';
        $factory = new StorageFactory($name);

        $objectManager  = $this->createMock(ObjectManager::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setInvokableClass(
            'DoctrineModule\Authentication\Storage\Session',
            Session::class
        );
        $serviceManager->setService(
            'Configuration',
            [
                'doctrine' => [
                    'authentication' => [
                        $name => [
                            'objectManager'      => $objectManager,
                            'identityClass'      => IdentityObject::class,
                            'identityProperty'   => 'username',
                            'credentialProperty' => 'password',
                        ],
                    ],
                ],
            ]
        );

        $adapter = $factory($serviceManager, ObjectRepository::class);
        $this->assertInstanceOf(ObjectRepository::class, $adapter);
    }

    public function testCanInstantiateStorageFromServiceLocator()
    {
        $factory        = new StorageFactory('testFactory');
        /** @var \PHPUnit_Framework_MockObject_MockObject|ServiceManager $serviceManager */
        $serviceManager = $this->createMock(ServiceManager::class);
        $storage        = $this->createMock(StorageInterface::class);
        $config         = [
            'doctrine' => [
                'authentication' => [
                    'testFactory' => ['storage' => 'some_storage'],
                ],
            ],
        ];

        $serviceManager
            ->expects($this->at(0))
            ->method('get')
            ->with('Configuration')
            ->will($this->returnValue($config));
        $serviceManager
            ->expects($this->at(1))
            ->method('get')
            ->with('some_storage')
            ->will($this->returnValue($storage));

        $this->assertInstanceOf(ObjectRepository::class, $factory($serviceManager, ObjectRepository::class));
    }
}
