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
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
use PHPUnit_Framework_TestCase as BaseTestCase;

/**
 * Base test case to be used when a service manager instance is required
 */
class ObjectManagerInitializerTest extends BaseTestCase
{

	/**
	 * @var ServiceManager
	 */
	protected $services;

	/**
	 * @var ControllerManager
	 */
	protected $controllers;

	/**
	 * @var EventManager
	 */
	protected $events;

	/**
	 * @var SharedEventManager
	 */
	protected $sharedEvents;

	/**
	 * @var PluginManager
	 */
	protected $plugins;

	public function setUp()
	{
		// setup EventManager
		$this->events = new EventManager();
		$this->sharedEvents = new SharedEventManager;
		$this->events->setSharedManager($this->sharedEvents);

		// setup PluginManager
		$this->plugins  = new PluginManager();

		// setup ServiceManager
		$this->services = new ServiceManager();
		$this->services->setService('EventManager', $this->events);
		$this->services->setService('SharedEventManager', $this->sharedEvents);
		$this->services->setService('Zend\ServiceManager\ServiceLocatorInterface', $this->services);
		$this->services->setService('ControllerPluginManager', $this->plugins);
		$this->services->addInitializer(new ObjectManagerInitializer('Doctrine\Common\Persistence\ObjectManager'));

		// setup ControllerManager
		$this->controllers = new ControllerManager();
		$this->controllers->setServiceLocator($this->services);
		$this->controllers->addInitializer(new ObjectManagerInitializer('Doctrine\Common\Persistence\ObjectManager'));
	}

	public function testWillSetAndGetServiceName()
	{
		// get ObjectmanagerInitializer
		$initializer = new ObjectManagerInitializer('Doctrine\Common\Persistence\ObjectManager');
		$this->assertEquals('Doctrine\Common\Persistence\ObjectManager', $initializer->getServiceName());
	}

	public function testWillInitializeControllerWithObjectManager()
	{
		// get mock for Doctrine\Common\Persistence\ObjectManager
		$objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

		// configure service manager
		$this->services->setFactory('Doctrine\Common\Persistence\ObjectManager', function() use ($objectManager) {
			return $objectManager;
		});

		// configure controller manager
		$this->controllers->setFactory('TestAsset\DummyObjectManagerAwareController', function() {
			return new TestAsset\DummyObjectManagerAwareController();
		});

		/** @var TestAsset\DummyObjectManagerAwareController $controller get controller */
		$controller = $this->controllers->get('TestAsset\DummyObjectManagerAwareController');
		$this->assertSame($objectManager, $controller->getObjectManager());
	}

	public function testWillInitializeControllerWithoutObjectManagerThrowException()
	{
		// get mock for Doctrine\Common\Persistence\ObjectRepository (to throw exception)
		$objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

		// configure service manager
		$this->services->setFactory('Doctrine\Common\Persistence\ObjectManager', function() use ($objectManager) {
			return $objectManager;
		});

		// configure controller manager
		$this->controllers->setFactory('TestAsset\DummyObjectManagerAwareController', function() {
			return new TestAsset\DummyObjectManagerAwareController();
		});

		// get controller and check ServiceNotFoundException is throw
		$this->setExpectedException('Zend\ServiceManager\Exception\ServiceNotFoundException');
		$this->controllers->get('TestAsset\DummyObjectManagerAwareController');
	}

	public function testWillInitializeServiceWithObjectManager()
	{
		// get mock for Doctrine\Common\Persistence\ObjectManager
		$objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

		// configure service manager
		$this->services->setFactory('Doctrine\Common\Persistence\ObjectManager', function() use ($objectManager) {
			return $objectManager;
		});
		$this->services->setFactory('TestAsset\DummyObjectManagerAwareService', function() {
			return new TestAsset\DummyObjectManagerAwareService();
		});

		/** @var TestAsset\DummyObjectManagerAwareService $service get service */
		$service = $this->services->get('TestAsset\DummyObjectManagerAwareService');
		$this->assertSame($objectManager, $service->getObjectManager());
	}

	public function testWillInitializeServiceWithoutObjectManagerThrowException()
	{
		// get mock for Doctrine\Common\Persistence\ObjectRepository (to throw exception)
		$objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

		// configure service manager
		$this->services->setFactory('Doctrine\Common\Persistence\ObjectManager', function() use ($objectManager) {
			return $objectManager;
		});
		$this->services->setFactory('TestAsset\DummyObjectManagerAwareService', function() {
			return new TestAsset\DummyObjectManagerAwareService();
		});

		// get service and check ServiceNotFoundException is throw
		$this->setExpectedException('Zend\ServiceManager\Exception\ServiceNotFoundException');
		$this->services->get('TestAsset\DummyObjectManagerAwareService');
	}

}
