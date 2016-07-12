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

use Interop\Container\ContainerInterface;
use RuntimeException;
use Zend\ServiceManager\FactoryInterface;

/**
 * Base ServiceManager factory to be extended
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * Would normally be set to orm | odm
     *
     * @var string
     */
    protected $mappingType;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Zend\Stdlib\AbstractOptions
     */
    protected $options;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Would normally be set to orm | odm
     *
     * @return string
     */
    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * Gets options from configuration based on name.
     *
     * @param  ContainerInterface $container
     * @param  string             $key
     * @param  null|string        $name
     * @return \Zend\Stdlib\AbstractOptions
     * @throws \RuntimeException
     */
    public function getOptions(ContainerInterface $container, $key, $name = null)
    {
        if ($name === null) {
            $name = $this->getName();
        }

        $options = $container->get('Configuration');
        $options = $options['doctrine'];
        if ($mappingType = $this->getMappingType()) {
            $options = $options[$mappingType];
        }
        $options = isset($options[$key][$name]) ? $options[$key][$name] : null;

        if (null === $options) {
            throw new RuntimeException(
                sprintf(
                    'Options with name "%s" could not be found in "doctrine.%s".',
                    $name,
                    $key
                )
            );
        }

        $optionsClass = $this->getOptionsClass();

        return new $optionsClass($options);
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @abstract
     * @return string
     */
    abstract public function getOptionsClass();
}
