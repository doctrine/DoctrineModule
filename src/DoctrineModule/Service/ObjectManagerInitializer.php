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
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Service initializer that is capable of injecting an {@see \Doctrine\Common\Persistence\ObjectManager}
 * into {@see \DoctrineModule\Persistence\ObjectManagerAwareInterface} services
 *
 * @package DoctrineModule\Service
 */
class ObjectManagerInitializer implements InitializerInterface
{
    /**
     * @var string
     */
    protected $serviceName;

    /**
     * Class constructor
     *
     * @param string $serviceName Service name to retrieve ObjectManager instance
     */
    public function __construct($serviceName)
    {
        $this->serviceName = (string) $serviceName;
    }

    /**
     * Initialize instance according ObjectManagerAwareInterface
     *
     * @param                                              $instance
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return mixed|void
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof ObjectManagerAwareInterface) {
            $instance->setObjectManager($this->getObjectManager($serviceLocator));
        }
    }

    /**
     * Get ObjectManager instance from ServiceLocatorInterface
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    protected function getObjectManager(ServiceLocatorInterface $serviceLocator)
    {
        $objectManager = null;

        if ($serviceLocator->has($this->serviceName)) {
            $objectManager = $serviceLocator->get($this->serviceName);
        }

        if (
            !($objectManager instanceof ObjectManager)
            && $serviceLocator instanceof ServiceLocatorAwareInterface
            && $serviceLocator->getServiceLocator()
            && $serviceLocator->getServiceLocator()->has($this->serviceName)
        ) {
            $objectManager = $serviceLocator->getServiceLocator()->get($this->serviceName);
        }

        if ($objectManager instanceof ObjectManager) {
            return $objectManager;
        }

        throw new ServiceNotFoundException(sprintf(
            'Retrieved service "%s" is not an instance of Doctrine\Common\Persistence\ObjectManager, "%s" given',
            $this->serviceName,
            is_object($objectManager) ? get_class($objectManager) : gettype($objectManager)
        ));
    }
}
