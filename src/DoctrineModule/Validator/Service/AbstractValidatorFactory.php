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

namespace DoctrineModule\Validator\Service;

use Zend\ServiceManager\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use DoctrineModule\Validator\Service\Exception\ServiceCreationException;
use Zend\Stdlib\ArrayUtils;

/**
 * Factory for creating NoObjectExists instances
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   1.3.0
 * @author  Fabian Grutschus <f.grutschus@lubyte.de>
 */
abstract class AbstractValidatorFactory implements FactoryInterface
{
    const DEFAULT_OBJECTMANAGER_KEY = 'doctrine.entitymanager.orm_default';

    protected $creationOptions = [];

    protected $validatorClass;

    /**
     * @param ContainerInterface $container
     * @param array $options
     * @return \Doctrine\Common\Persistence\ObjectRepository
     * @throws ServiceCreationException
     */
    protected function getRepository(ContainerInterface $container, array $options)
    {
        if (empty($options['target_class'])) {
            throw new ServiceCreationException(sprintf(
                "Option 'target_class' is missing when creating validator %s",
                __CLASS__
            ));
        }

        $objectManager    = $this->getObjectManager($container, $options);
        $targetClassName  = $options['target_class'];
        $objectRepository = $objectManager->getRepository($targetClassName);

        return $objectRepository;
    }

    /**
     * @param ContainerInterface $container
     * @param array $options
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager(ContainerInterface $container, array $options)
    {
        $objectManager = isset($options['object_manager']) ? $options['object_manager'] : self::DEFAULT_OBJECTMANAGER_KEY;
        if (is_string($objectManager)) {
            $objectManager = $container->get($objectManager);
        }

        return $objectManager;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getFields(array $options)
    {
        if (isset($options['fields'])) {
            return (array) $options['fields'];
        }

        return [];
    }

    /**
     * Helper to merge options array passed to `__invoke`
     * together with the options array created based on the above
     * helper methods.
     *
     * @param array $previousOptions
     * @param array $newOptions
     * @return array
     */
    protected function merge($previousOptions, $newOptions)
    {
        return ArrayUtils::merge($previousOptions, $newOptions, true);
    }

    /**
     * Helper method for ZF2 compatiblity.
     *
     * In ZF2 the plugin manager instance if passed to `createService`
     * instead of the global service manager instance (as in ZF3).
     *
     * @param ContainerInterface $container
     * @return ContainerInterface
     */
    protected function container(ContainerInterface $container)
    {
        if ($container instanceof ServiceLocatorAwareInterface) {
            $container = $container->getServiceLocator();
        }

        return $container;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, $this->validatorClass, $this->creationOptions);
    }

    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
