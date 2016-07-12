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

namespace DoctrineModule\ServiceFactory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract service factory capable of instantiating services whose names match the
 * pattern <code>doctrine.$serviceType.$serviceName</doctrine>
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class AbstractDoctrineServiceFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return false !== $this->getFactoryMapping($container, $requestedName);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mappings = $this->getFactoryMapping($container, $requestedName);

        if (! $mappings) {
            throw new ServiceNotFoundException();
        }

        $factoryClass = $mappings['factoryClass'];
        /* @var $factory \DoctrineModule\Service\AbstractFactory */
        $factory = new $factoryClass($mappings['serviceName']);

        return $factory->createService($container);
    }

    /**
     * {@inheritDoc}
     * @deprecated
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this->canCreate($container, $requestedName);
    }

    /**
     * {@inheritDoc}
     * @deprecated
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this($serviceLocator, $requestedName);
    }

    /**
     * @param ContainerInterface $serviceLocator
     * @param string             $name
     *
     * @return array|bool
     */
    private function getFactoryMapping(ContainerInterface $serviceLocator, $name)
    {
        $matches = array();

        if (!preg_match(
            '/^doctrine\.((?<mappingType>orm|odm)\.|)(?<serviceType>[a-z0-9_]+)\.(?<serviceName>[a-z0-9_]+)$/',
            $name,
            $matches
        )) {
            return false;
        }

        $config      = $serviceLocator->get('Config');
        $mappingType = $matches['mappingType'];
        $serviceType = $matches['serviceType'];
        $serviceName = $matches['serviceName'];

        if ($mappingType == '') {
            if (! isset($config['doctrine_factories'][$serviceType]) ||
                 ! isset($config['doctrine'][$serviceType][$serviceName])
            ) {
                return false;
            }

            return array(
                'serviceType'  => $serviceType,
                'serviceName'  => $serviceName,
                'factoryClass' => $config['doctrine_factories'][$serviceType],
            );
        } else {
            if (! isset($config['doctrine_factories'][$mappingType]) ||
                 ! isset($config['doctrine_factories'][$mappingType][$serviceType]) ||
                 ! isset($config['doctrine'][$mappingType][$serviceType][$serviceName])
            ) {
                return false;
            }
            return array(
                'serviceType'  => $serviceType,
                'serviceName'  => $serviceName,
                'factoryClass' => $config['doctrine_factories'][$mappingType][$serviceType],
            );
        }
    }
}
