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

use DoctrineModule\Service\Authentication\StorageFactory;
use PHPUnit_Framework_TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

class StorageFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
    {
        $name = 'testFactory';
        $factory = new StorageFactory($name);

        $objectManager =  $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $serviceManager = new ServiceManager();
        $serviceManager->setInvokableClass(
            'DoctrineModule\Authentication\Storage\Session',
            'Zend\Authentication\Storage\Session'
        );
        $serviceManager->setService(
            'Configuration',
            array(
                'doctrine' => array(
                    'authentication' => array(
                        $name => array(
                            'objectManager' => $objectManager,
                            'identityClass' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
                            'identityProperty' => 'username',
                            'credentialProperty' => 'password'
                        ),
                    ),
                ),
            )
        );

        $adapter = $factory->createService($serviceManager);
        $this->assertInstanceOf('DoctrineModule\Authentication\Storage\ObjectRepository', $adapter);
    }

    public function testCanInstantiateStorageFromServiceLocator()
    {
        $factory        = new StorageFactory('testFactory');
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $storage        = $this->getMock('Zend\Authentication\Storage\StorageInterface');
        $config         = array(
            'doctrine' => array(
                'authentication' => array(
                    'testFactory' => array('storage' => 'some_storage')
                ),
            )
        );

        $serviceLocator
            ->expects($this->at(0))
            ->method('get')
            ->with('Configuration')
            ->will($this->returnValue($config));
        $serviceLocator
            ->expects($this->at(1))
            ->method('get')
            ->with('some_storage')
            ->will($this->returnValue($storage));


        $this->assertInstanceOf(
            'DoctrineModule\Authentication\Storage\ObjectRepository',
            $factory->createService($serviceLocator)
        );
    }
}
