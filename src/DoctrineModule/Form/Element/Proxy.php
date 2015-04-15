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

namespace DoctrineModule\Form\Element;

use DoctrineModule\Exception\InvalidArgumentException;
use DoctrineModule\Exception\RuntimeException;
use ReflectionMethod;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Stdlib\Hydrator\Filter\OptionalParametersFilter;

class Proxy implements ObjectManagerAwareInterface
{
    /**
     * @var array
     */
    protected $objects;

    /**
     * @var string
     */
    protected $targetClass;

    /**
     * @var array
     */
    protected $valueOptions = array();

    /**
     * @var array
     */
    protected $findMethod = array();

    /**
     * @var
     */
    protected $property;

    /**
     * @var callable $labelGenerator A callable used to create a label based on an item in the collection an Entity
     */
    protected $labelGenerator;

    /**
     * @var
     */
    protected $isMethod;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function setOptions($options)
    {
        if (isset($options['object_manager'])) {
            $this->setObjectManager($options['object_manager']);
        }

        if (isset($options['target_class'])) {
            $this->setTargetClass($options['target_class']);
        }

        if (isset($options['property'])) {
            $this->setProperty($options['property']);
        }

        if (isset($options['label_generator'])) {
            $this->setLabelGenerator($options['label_generator']);
        }

        if (isset($options['find_method'])) {
            $this->setFindMethod($options['find_method']);
        }

        if (isset($options['is_method'])) {
            $this->setIsMethod($options['is_method']);
        }
    }

    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->loadValueOptions();
        }

        return $this->valueOptions;
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        $this->loadObjects();

        return $this->objects;
    }

    /**
     * Set the object manager
     *
     * @param  ObjectManager  $objectManager
     * @return Proxy
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Set the FQCN of the target object
     *
     * @param  string         $targetClass
     * @return Proxy
     */
    public function setTargetClass($targetClass)
    {
        $this->targetClass = $targetClass;
    }

    /**
     * Get the target class
     *
     * @return string
     */
    public function getTargetClass()
    {
        return $this->targetClass;
    }

    /**
     * Set the property to use as the label in the options
     *
     * @param  string         $property
     * @return Proxy
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set the label generator callable that is responsible for generating labels for the items in the collection
     *
     * @param callable $callable A callable used to create a label based off of an Entity
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function setLabelGenerator($callable)
    {
        if (! is_callable($callable)) {
            throw InvalidArgumentException::invalidLabelGenerator(__METHOD__, __LINE__);
        }

        $this->labelGenerator = $callable;
    }

    /**
     * @return callable|null
     */
    public function getLabelGenerator()
    {
        return $this->labelGenerator;
    }

    /**
     * Set if the property is a method to use as the label in the options
     *
     * @param  boolean         $method
     * @return Proxy
     */
    public function setIsMethod($method)
    {
        $this->isMethod = (bool) $method;
    }

    /**
     * @return mixed
     */
    public function getIsMethod()
    {
        return $this->isMethod;
    }

    /** Set the findMethod property to specify the method to use on repository
     *
     * @param array $findMethod
     * @return Proxy
     */
    public function setFindMethod($findMethod)
    {
        $this->findMethod = $findMethod;
    }

    /**
     * Get findMethod definition
     *
     * @return array
     */
    public function getFindMethod()
    {
        return $this->findMethod;
    }

    /**
     * @param $targetEntity
     * @return string|null
     */
    protected function generateLabel($targetEntity)
    {
        if (null === ($labelGenerator = $this->getLabelGenerator())) {
            return null;
        }

        return call_user_func($labelGenerator, $targetEntity);
    }

    /**
     * @param  $value
     * @return array|mixed|object
     * @throws RuntimeException  If no object manager is set.
     * @throws RuntimeException  If no target class has been set.
     */
    public function getValue($value)
    {
        $objectManager = $this->getObjectManager();

        if (!$objectManager) {
            throw RuntimeException::objectManagerNotSet(__METHOD__, __LINE__);
        }

        if (!$this->getTargetClass()) {
            throw RuntimeException::targetClassNotSet(__METHOD__, __LINE__);
        }

        $metadata = $objectManager->getClassMetadata($this->getTargetClass());

        if (!is_object($value)) {
            return $value;
        }

        if ($value instanceof Collection) {
            return array_map(
                function ($object) use ($metadata) {
                    $identifier = $metadata->getIdentifierValues($object);

                    if (count($identifier) > 1) {
                        throw RuntimeException::multipleIdentifiers(__METHOD__, __LINE__);
                    }

                    return reset($identifier);
                },
                $value->toArray()
            );
        }

        $metadata   = $objectManager->getClassMetadata(get_class($value));
        $identifier = $metadata->getIdentifierFieldNames();

        // TODO: handle composite (multiple) identifiers
        if (count($identifier) > 1) {
            throw RuntimeException::multipleIdentifiers(__METHOD__, __LINE__);
        }

        $value = current($metadata->getIdentifierValues($value));

        return $value;
    }

    /**
     * Load objects
     *
     * @throws RuntimeException
     *
     * @return void
     */
    protected function loadObjects()
    {
        if (!empty($this->objects)) {
            return;
        }

        $findMethod = (array) $this->getFindMethod();

        if (!$findMethod) {
            // No find method was specified so fetchAll objects and finish
            $this->objects = $this->objectManager->getRepository($this->targetClass)->findAll();

            return null;
        }

        if (!isset($findMethod['name'])) {
            throw RuntimeException::findMethodNameNotSet(__METHOD__, __LINE__);
        }

        $findMethodName   = $findMethod['name'];
        $findMethodParams = isset($findMethod['params']) ? array_change_key_case($findMethod['params']) : null;

        $repository = $this->objectManager->getRepository($this->targetClass);

        if (!method_exists($repository, $findMethodName)) {
            throw RuntimeException::invalidFindMethodName(
                $findMethodName,
                get_class($repository),
                __METHOD__,
                __LINE__
            );
        }

        $this->objects = $this->callMethodWithParameters($repository, $findMethodName, $findMethodParams);
    }

    /**
     * Load value options
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function loadValueOptions()
    {
        if (!$this->objectManager) {
            throw RuntimeException::objectManagerNotSet(__METHOD__, __LINE__);
        }

        if (!$this->targetClass) {
            throw RuntimeException::targetClassNotSet(__METHOD__, __LINE__);
        }

        $metadata   = $this->objectManager->getClassMetadata($this->targetClass);
        $identifier = $metadata->getIdentifierFieldNames();
        $objects    = $this->getObjects();
        $options    = array();

        if (empty($objects)) {
            $this->valueOptions = array('' => '');

            return;
        }

        $multipleIdentifiers = count($identifier) > 1;

        foreach ($objects as $key => $object) {
            $identifier = $metadata->getIdentifierValues($object);

            $value = $multipleIdentifiers ? $key : reset($identifier);

            $options[] = array(
                'label' => $this->getLabel($object, $metadata),
                'value' => $value
            );
        }

        $this->valueOptions = $options;
    }

    /**
     * Call method $methodName on $object matching the parameters in $methodParams
     * to the function arguments by name.
     *
     * @param  object $object
     * @param  string $methodName
     * @param  array  $methodParams
     * @return void
     */
    private function callMethodWithParameters($object, $methodName, array $methodParams)
    {
        $reflectionMethod = new ReflectionMethod($object, $methodName);
        $args = array();

        foreach ($reflectionMethod->getParameters() as $param) {
            if (array_key_exists(strtolower($param->getName()), $methodParams)) {
                $args[] = $methodParams[strtolower($param->getName())];
                continue;
            }

            $args[] = $param->getDefaultValue();
        }

        return $reflectionMethod->invokeArgs($object, $args);
    }

    /**
     * Return the label for the given object.
     *
     * @param  object           $object
     * @param  ClassMetadata    $metadata
     * @return string
     * @throws RuntimeException If requested label property doesn't exist.
     * @throws RuntimeException If requested label property getter method isn't callable.
     * @throws RuntimeException If the __toString method isn't implemented.
     */
    protected function getLabel($object, ClassMetadata $metadata)
    {
        $generatedLabel = $this->generateLabel($object);

        if (null !== $generatedLabel) {
            return $generatedLabel;
        }

        $property = $this->property;

        if ($property) {
            if (false == $this->isMethod && !$metadata->hasField($property)) {
                throw RuntimeException::invalidPropertyName(
                    $property,
                    $this->targetClass,
                    __METHOD__,
                    __LINE__
                );
            }

            $getter = 'get' . ucfirst($property);

            $optionalParametersFilter = new OptionalParametersFilter();

            if (!$optionalParametersFilter->filter(get_class($object) . '::' . $getter)) {
                throw RuntimeException::methodNotCallable(
                    $this->targetClass . '::' . $getter,
                    __METHOD__,
                    __LINE__
                );
            }

            return $object->{$getter}();
        }

        if (!is_callable(array($object, '__toString'))) {
            throw RuntimeException::noMethodOrToString(
                $this->targetClass,
                __METHOD__,
                __LINE__
            );
        }

        return (string) $object;
    }
}
