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

namespace DoctrineModule\Factory;

use InvalidArgumentException;
use Doctrine\Common\Annotations;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * MappingDriver factory
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class DriverFactory implements AbstractFactoryInterface, ServiceLocatorAwareInterface
{

    const OPTIONS_CLASS = '\DoctrineModule\Options\Driver';

    protected $serviceLocator;

    /**
     * {@inheritDoc}
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    /**
     * {@inheritDoc}
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritDoc}
     * @return MappingDriver
     */
    public function create($options)
    {

        $optionsClass = self::OPTIONS_CLASS;

        if (is_array($options) || $options instanceof \Traversable){
            $options = new $optionsClass($options);
        } else if ( ! $options instanceof $optionsClass){
            throw new \InvalidArgumentException();
        }

        $class = $options->getClass();

        if (!$class) {
            throw new InvalidArgumentException('Drivers must specify a class');
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Driver with type "%s" could not be found', $class));
        }

        // Not all drivers (DriverChain) require paths.
        $paths = $options->getPaths();

        // Special options for AnnotationDrivers.
        if ('Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver' === $class
            || is_subclass_of($class, 'Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver')
        ) {
            $reader = new Annotations\AnnotationReader;
            $reader = new Annotations\CachedReader(
                new Annotations\IndexedReader($reader),
                $this->serviceLocator->get($options->getCache())
            );
            /* @var $driver MappingDriver */
            $driver = new $class($reader, $paths);
        } else {
            /* @var $driver MappingDriver */
            $driver = new $class($paths);
        }

        if ($options->getExtension() && $driver instanceof FileDriver) {
            /* @var $driver FileDriver */
            /* @var $locator \Doctrine\Common\Persistence\Mapping\Driver\FileLocator */
            $locator = $driver->getLocator();

            if (get_class($locator) === 'Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator') {
                $driver->setLocator(new DefaultFileLocator($locator->getPaths(), $options->getExtension()));
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'Discovered file locator for driver of type "%s" is an instance of "%s". This factory '
                        . 'supports only the DefaultFileLocator when an extension is set for the file locator',
                        get_class($driver),
                        get_class($locator)
                    )
                );
            }
        }

        // Extra post-create options for DriverChain.
        if ($driver instanceof MappingDriverChain && $options->getDrivers()) {
            /* @var $driver \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain */
            $drivers = $options->getDrivers();

            if (!is_array($drivers)) {
                $drivers = array($drivers);
            }

            foreach ($drivers as $namespace => $childDriver) {
                if (null === $childDriver) {
                    continue;
                }
                if (is_string($childDriver)){
                    $driver->addDriver($this->serviceLocator->get($childDriver), $namespace);
                    continue;
                }
                $driver->addDriver($childDriver, $namespace);
            }
        }

        return $driver;
    }
}
