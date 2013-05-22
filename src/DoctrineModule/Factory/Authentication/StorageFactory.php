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
namespace DoctrineModule\Factory\Authentication;

use DoctrineModule\Authentication\Storage\ObjectRepository as Storage;
use DoctrineModule\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create authentication storage object.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StorageFactory implements AbstractFactoryInterface, ServiceLocatorAwareInterface
{
    const OPTIONS_CLASS = 'DoctrineModule\Options\Authentication\Storage';
    
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * {@inheritDoc}
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * {@inheritDoc}
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritDoc}
     *
     * @return \DoctrineModule\Authentication\Storage\ObjectRepository
     */
    public function create($options)
    {

        $optionsClass = self::OPTIONS_CLASS;

        if (is_array($options) || $options instanceof \Traversable) {
            /* @var $options \DoctrineModule\Options\Authentication\Storage */
            $options = new $optionsClass($options);
        } elseif ( ! $options instanceof $optionsClass){
            throw new \InvalidArgumentException();
        }

        if (is_string($objectManager = $options->getObjectManager())) {
            $options->setObjectManager($this->serviceLocator->get($objectManager));
        }

        if (is_string($storage = $options->getStorage())) {
            $options->setStorage($this->serviceLocator->get($storage));
        }

        return new Storage($options);
    }
}
