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

use DoctrineModule\Service\ObjectManagerInitializer;
use Zend\ServiceManager\ServiceManager;
use PHPUnit_Framework_TestCase as BaseTestCase;

/**
 * Base test case to be used when a service manager instance is required
 */
class ObjectManagerInitializerTest extends BaseTestCase
{
    /**
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::__construct
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::initialize
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::getObjectManager
     */
    public function testInitialize()
    {
        $initializer    = new ObjectManagerInitializer('test-object-manager');
        $serviceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $objectManager  = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $instance    = $this->getMock('DoctrineModule\\Persistence\\ObjectManagerAwareInterface');

        $instance
            ->expects($this->once())
            ->method('setObjectManager')
            ->with($objectManager);

        $serviceLocator
            ->expects($this->any())
            ->method('has')
            ->with('test-object-manager')
            ->will($this->returnValue(true));

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('test-object-manager')
            ->will($this->returnValue($objectManager));

        $initializer->initialize($instance, $serviceLocator);
    }

    /**
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::__construct
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::initialize
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::getObjectManager
     */
    public function testInitializesWithPluginManagerWithParentServiceManager()
    {
        $initializer    = new ObjectManagerInitializer('test-object-manager');
        $pluginManager  = $this->getMock('Zend\\ServiceManager\\AbstractPluginManager');
        $serviceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $objectManager  = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $instance       = $this->getMock('DoctrineModule\\Persistence\\ObjectManagerAwareInterface');

        $instance
            ->expects($this->once())
            ->method('setObjectManager')
            ->with($objectManager);

        $pluginManager
            ->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocator));

        $serviceLocator
            ->expects($this->any())
            ->method('has')
            ->with('test-object-manager')
            ->will($this->returnValue(true));

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('test-object-manager')
            ->will($this->returnValue($objectManager));

        $initializer->initialize($instance, $pluginManager);
    }

    /**
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::__construct
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::initialize
     * @covers \DoctrineModule\Service\ObjectManagerInitializer::getObjectManager
     */
    public function testDisallowsInvalidObjectManager()
    {
        $initializer    = new ObjectManagerInitializer('test-object-manager');
        $serviceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $instance       = $this->getMock('DoctrineModule\\Persistence\\ObjectManagerAwareInterface');

        $instance
            ->expects($this->never())
            ->method('setObjectManager');

        $serviceLocator
            ->expects($this->any())
            ->method('has')
            ->with('test-object-manager')
            ->will($this->returnValue(true));

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('test-object-manager')
            ->will($this->returnValue(new \stdClass()));

        $this->setExpectedException('Zend\\ServiceManager\\Exception\\ServiceNotFoundException');
        $initializer->initialize($instance, $serviceLocator);
    }
}
