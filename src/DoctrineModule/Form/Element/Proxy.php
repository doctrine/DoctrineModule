<?php

namespace DoctrineModule\Form\Element;

use RuntimeException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;

class Proxy
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
     * @var
     */
    protected $property;

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
                foreach($value as $object) {
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

    protected function loadObjects()
    {
        if (!empty($this->objects)) {
            return;
        }
        $this->objects = $this->objectManager->getRepository($this->targetClass)->findAll();
    }

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

        if (empty($objects)) {
            $options[''] = '';
        } else {
            foreach ($objects as $key => $object) {
                if (($property = $this->property)) {
                    if (!$metadata->hasField($property)) {
                        throw new RuntimeException(sprintf(
                            'Property "%s" could not be found in object "%s"',
                            $property,
                            $targetClass
                        ));
                    }

                    $getter = 'get' . ucfirst($property);
                    if (!is_callable(array($object, $getter))) {
                        throw new RuntimeException(sprintf(
                            'Method "%s::%s" is not callable',
                            $this->targetClass,
                            $getter
                        ));
                    }

                    $label = $object->{$getter}();
                } else {
                    if (!is_callable(array($object, '__toString'))) {
                        throw new RuntimeException(sprintf(
                            '%s must have a "__toString()" method defined if you have not set a property or method to use.',
                            $targetClass
                        ));
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
        }

        $this->valueOptions = $options;
    }
}