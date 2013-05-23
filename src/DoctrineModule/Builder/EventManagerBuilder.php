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

namespace DoctrineModule\Builder;

use InvalidArgumentException;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use DoctrineModule\Exception;
use DoctrineModule\Options\EventManagerOptions;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Builder responsible for creating EventManager instances
 */
class EventManagerBuilder implements AbstractBuilderInterface, ServiceLocatorAwareInterface
{
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
     */
    public function build($options)
    {
        if (is_array($options) || $options instanceof \Traversable) {
            $options = new EventManagerOptions($options);
        } elseif (! $options instanceof EventManagerOptions) {
            throw new Exception\InvalidArgumentException();
        }

        $eventManager = new EventManager();

        foreach ($options->getSubscribers() as $subscriberName) {
            $subscriber = $subscriberName;

            if (is_string($subscriber)) {
                if ($this->serviceLocator->has($subscriber)) {
                    $subscriber = $this->serviceLocator->get($subscriber);
                } elseif (class_exists($subscriber)) {
                    $subscriber = new $subscriber();
                }
            }

            if ($subscriber instanceof EventSubscriber) {
                $eventManager->addEventSubscriber($subscriber);
                continue;
            }

            $subscriberType = is_object($subscriberName) ? get_class($subscriberName) : $subscriberName;
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid event subscriber "%s" given, must be a service name, '
                    . 'class name or an instance implementing Doctrine\Common\EventSubscriber',
                    is_string($subscriberType) ? $subscriberType : gettype($subscriberType)
                )
            );
        }

        return $eventManager;
    }
}
