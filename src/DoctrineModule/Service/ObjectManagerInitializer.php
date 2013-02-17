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

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Mvc\Exception\DomainException;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectManagerInitializer implements InitializerInterface
{

	/**
	 * @var string
	 */
	protected $serviceName;

	/**
	 * Class constructor
	 * @access public
	 * @param string $serviceName Service name to retrieve ObjectManager instance
	 */
	public function __construct($serviceName)
	{
		// set service name to use
		$this->setServiceName($serviceName);
	}

	/**
	 * Initialize instance according ObjectManagerAwareInterface
	 * @access public
	 * @param ObjectManagerAwareInterface $instance
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return void
	 */
	public function initialize($instance, ServiceLocatorInterface $serviceLocator)
	{
		// check we have an ObjectManagerAwareInterface instance
		if ($instance instanceof ObjectManagerAwareInterface) {

			// get ObjectManager instance
			$objectManager = $this->getObjectManager($serviceLocator);

			// set ObjectManager to instance
			$instance->setObjectManager($objectManager);
		}
	}

	/**
	 * Get ObjectManager instance from ServiceLocatorInterface
	 * @access protected
	 * @param ServiceLocatorInterface $serviceLocator
	 * @throws \Zend\Mvc\Exception\DomainException
	 * @return ObjectManager
	 */
	protected function getObjectManager(ServiceLocatorInterface $serviceLocator)
	{
		// setup ObjectManager instance to null
		$objectManager = null;

		// check we have a AbstractPluginManager instance
		if ($serviceLocator instanceof AbstractPluginManager) {

			// get ObjectManager instance from AbstractPluginManager
			$objectManager = $serviceLocator->getServiceLocator()->get($this->getServiceName());

		// check we have a ServiceManager instance
		} else if ($serviceLocator instanceof ServiceManager) {

			// get ObjectManager instance from ServiceManager
			$objectManager = $serviceLocator->get($this->getServiceName());
		}

		// check we have ObjectManager instance
		if (!($objectManager instanceof ObjectManager)) {

			// throw exception
			throw new DomainException('Unable to retrieve instance of "\Doctrine\Common\Persistence\ObjectManager"');
		}

		// return ObjectManager instance
		return $objectManager;
	}

	/**
	 * Set service name to retrieve ObjectManager instance
	 * @access public
	 * @param string $serviceName Service name to retrieve ObjectManager instance
	 * @return string
	 */
	public function setServiceName($serviceName)
	{
		// store service name used to retrieve ObjectManager
		$this->serviceName = $serviceName;
		return $this;
	}

	/**
	 * Get service name to retrieve ObjectManager instance
	 * @access public
	 * @return string
	 */
	public function getServiceName()
	{
		// return service name used to retrieve ObjectManager
		return $this->serviceName;
	}

}
