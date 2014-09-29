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

use InvalidArgumentException;
use RuntimeException;
use ReflectionMethod;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Traversable;
use Zend\Stdlib\Guard\GuardUtils;
use Doctrine\ORM\QueryBuilder;

class Proxy implements ObjectManagerAwareInterface
{
    /**
     * @var array|Traversable
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

    /**
     * @var bool
     */
    protected $displayEmptyItem = false;

    /**
     * @var string
     */
    protected $emptyItemLabel = '';

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

        if (isset($options['display_empty_item'])) {
            $this->setDisplayEmptyItem($options['display_empty_item']);
        }

        if (isset($options['empty_item_label'])) {
            $this->setEmptyItemLabel($options['empty_item_label']);
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
     * @return array|Traversable
     */
    public function getObjects()
    {
        $this->loadObjects();

        return $this->objects;
    }

    /**
     * Set the label for the empty option
     *
     * @param string          $emptyItemLabel
     * @return Proxy
     */
    public function setEmptyItemLabel($emptyItemLabel)
    {
        $this->emptyItemLabel = $emptyItemLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyItemLabel()
    {
        return $this->emptyItemLabel;
    }

    /**
     * Set a flag, whether to include the empty option at the beginning or not
     *
     * @param boolean         $displayEmptyItem
     * @return Proxy
     */
    public function setDisplayEmptyItem($displayEmptyItem)
    {
        $this->displayEmptyItem = $displayEmptyItem;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisplayEmptyItem()
    {
        return $this->displayEmptyItem;
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

        return $this;
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

        return $this;
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

        return $this;
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
            throw new InvalidArgumentException(
                'Property "label_generator" needs to be a callable function or a \Closure'
            );
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

        return $this;
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

        return $this;
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
     * @throws RuntimeException
     */
    public function getValue($value)
    {
        if (!($om = $this->getObjectManager())) {
            throw new RuntimeException('No object manager was set');
        }

        if (!($targetClass = $this->getTargetClass())) {
            throw new RuntimeException('No target class was set');
        }

        $metadata = $om->getClassMetadata($targetClass);
        if (is_object($value)) {
            if ($value instanceof Collection) {
                $data = array();
                foreach ($value as $object) {
                    $values = $metadata->getIdentifierValues($object);
                    $data[] = array_shift($values);
                }

                $value = $data;
            } else {
                $metadata   = $om->getClassMetadata(get_class($value));
                $identifier = $metadata->getIdentifierFieldNames();

                // TODO: handle composite (multiple) identifiers
                if (count($identifier) > 1) {
                    //$value = $key;
                } else {
                    $value = current($metadata->getIdentifierValues($value));
                }
            }
        }

        return $value;
    }

    /**
     * Load objects
     *
     * @throws RuntimeException
     * @throws Exception\InvalidRepositoryResultException
     * @return void
     */
    protected function loadObjects()
    {
        if (!empty($this->objects)) {
            return;
        }

        $findMethod = (array) $this->getFindMethod();
        if (!$findMethod) {
            $findMethodName = 'findAll';
            $repository = $this->objectManager->getRepository($this->targetClass);
            $objects = $repository->findAll();
        } else {
            if (!isset($findMethod['name'])) {
                throw new RuntimeException('No method name was set');
            }
            $findMethodName   = $findMethod['name'];
            $findMethodParams = isset($findMethod['params']) ? array_change_key_case($findMethod['params']) : array();

            $repository = $this->objectManager->getRepository($this->targetClass);
            if (!method_exists($repository, $findMethodName)) {
                throw new RuntimeException(
                    sprintf(
                        'Method "%s" could not be found in repository "%s"',
                        $findMethodName,
                        get_class($repository)
                    )
                );
            }

            $r    = new ReflectionMethod($repository, $findMethodName);
            $args = array();
            foreach ($r->getParameters() as $param) {
                if (array_key_exists(strtolower($param->getName()), $findMethodParams)) {
                    $args[] = $findMethodParams[strtolower($param->getName())];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } elseif (!$param->isOptional()) {
                    throw new RuntimeException(
                        sprintf(
                            'Required parameter "%s" with no default value for method "%s" in repository "%s"'
                            . ' was not provided',
                            $param->getName(),
                            $findMethodName,
                            get_class($repository)
                        )
                    );
                }
            }
            $objects = $r->invokeArgs($repository, $args);
            
            if($objects instanceof QueryBuilder){
                $objects = $objects->getQuery()->getResult();
            }
        }

        GuardUtils::guardForArrayOrTraversable(
            $objects,
            sprintf('%s::%s() return value', get_class($repository), $findMethodName),
            'DoctrineModule\Form\Element\Exception\InvalidRepositoryResultException'
        );

        $this->objects = $objects;
    }

    /**
     * Load value options
     *
     * @throws RuntimeException
     * @return void
     */
    protected function loadValueOptions()
    {
        if (!($om = $this->objectManager)) {
            throw new RuntimeException('No object manager was set');
        }

        if (!($targetClass = $this->targetClass)) {
            throw new RuntimeException('No target class was set');
        }

        $metadata   = $om->getClassMetadata($targetClass);
        $identifier = $metadata->getIdentifierFieldNames();
        $objects    = $this->getObjects();
        $options    = array();

        if ($this->displayEmptyItem) {
            $options[''] = $this->getEmptyItemLabel();
        }

        foreach ($objects as $key => $object) {
            if (null !== ($generatedLabel = $this->generateLabel($object))) {
                $label = $generatedLabel;
            } elseif ($property = $this->property) {
                if ($this->isMethod == false && !$metadata->hasField($property)) {
                    throw new RuntimeException(
                        sprintf(
                            'Property "%s" could not be found in object "%s"',
                            $property,
                            $targetClass
                        )
                    );
                }

                $getter = 'get' . ucfirst($property);
                if (!is_callable(array($object, $getter))) {
                    throw new RuntimeException(
                        sprintf('Method "%s::%s" is not callable', $this->targetClass, $getter)
                    );
                }

                $label = $object->{$getter}();
            } else {
                if (!is_callable(array($object, '__toString'))) {
                    throw new RuntimeException(
                        sprintf(
                            '%s must have a "__toString()" method defined if you have not set a property'
                            . ' or method to use.',
                            $targetClass
                        )
                    );
                }

                $label = (string) $object;
            }

            if (count($identifier) > 1) {
                $value = $key;
            } else {
                $value = current($metadata->getIdentifierValues($object));
            }

            $options[] = array('label' => $label, 'value' => $value);
        }

        $this->valueOptions = $options;
    }
}
