<?php

namespace DoctrineModule\Hydrator;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use ReflectionProperty;
use ReflectionClass;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator\HydratorInterface;

class RecursiveHydrator implements HydratorInterface
{

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var bool
     */
    protected $clone;

    /**
     * @var array
     */
    protected $extractFields = array();

    /**
     * @var bool
     */
    protected $extractSingleKeysFlat = false;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param bool $clone
     * @param array $extractFields
     * @param bool $extractSingleKeysFlat
     */
    public function __construct(
        ObjectManager $om,
        $clone = false,
        array $extractFields = array(),
        $extractSingleKeysFlat = false
    ) {
        $this->om = $om;
        $this->clone = (bool) $clone;
        $this->extractFields = $extractFields;
        $this->extractSingleKeysFlat = $extractSingleKeysFlat;
    }

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    public function extract($object)
    {
        if (!is_object($object)) {
            throw new Exception\InvalidArgumentException('$object must be an object');
        }
        $this->validateEntityName(get_class($object));
        return $this->toArray($object, $this->extractFields, $this->extractSingleKeysFlat);
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  object $object
     * @return object
     * @throws Exception\InvalidArgumentException
     */
    public function hydrate(array $data, $object)
    {
        if (!is_object($object)) {
            throw new Exception\InvalidArgumentException('$object must be an object');
        }
        return $this->getEntityFromArray($object, $data, $this->clone);
    }

    /**
     * Get entity from array
     *
     * @param object|string $entity
     * @param array $data
     * @param bool $clone
     * @return object
     * @throws Exception\InvalidArgumentException
     */
    protected function getEntityFromArray($entity, array $data, $clone = false)
    {
        if (is_string($entity) && false === $clone) {
            $this->validateEntityName($entity);
            $meta = $this->getObjectManager()->getClassMetadata($entity);
            $entity = $this->loadEntity($entity, $data, $clone);
        } else {
            $meta = $this->getObjectManager()->getClassMetadata(get_class($entity));
        }
        $identifier = array_shift($meta->getIdentifier());
        foreach ($data as $field => $value) {
            if ($field == $identifier
                && !$meta->generatorType !== 5 // GENERATOR_TYPE_NONE
            ) { // setting identifier is forbidden
                continue;
            }
            $field = $this->toCamelCase($field);
            if ($meta->hasField($field)) {
                $this->updateField($entity, $meta, $field, $value);
            } else if ($meta->hasAssociation($field)) {
                $this->updateAssociation($entity, $meta, $field, $value, $clone);
            } else {
                continue;
            }
        }
        return $entity;
    }

    /**
     * Convert a model class to an array recursively
     *
     * @param object|array $object
     * @param array $fields
     * @param bool $flastSingleKeys
     * @param array|bool $array (parameter only used during recursion)
     * @return array
     */
    public function toArray($object, array $fields = array(), $flatSingleKeys = false, $array = false)
    {
        $flatSingleKeys = (bool) $flatSingleKeys;
        if (empty($fields) && is_object($object)) {
            $reflection = new ReflectionClass(get_class($object));
            $properties = $reflection->getProperties();
            foreach($properties as $property) {
                $fields[] = $property->getName();
            }
        }
        $array = $array ?: $fields;
        foreach ($array as $key => $value) {
            foreach ($fields as $fieldName => $fieldValue) {
                if ($fieldName !== $key && $fieldName !== $value) {
                    continue;
                }
                if (!is_array($fieldValue)) {
                    unset($array[$fieldName]);
                    $fieldName = $fieldValue;
                    $fieldValue = array();
                } else {
                    unset($array[$fieldName]);
                }
                $key = $this->fromCamelCase($fieldName);
                $getter = $this->fieldToGetterMethod($key);
                if (is_callable(array($object, $getter))) {
                    $value = $object->$getter();
                } else if (property_exists(get_class($object), $key)) {
                    $reflectionProperty = new ReflectionProperty(get_class($object), $key);
                    $reflectionProperty->setAccessible(true);
                    $value = $reflectionProperty->getValue($object);
                } else {
                    continue;
                }
                if (is_object($value)) {
                    if ($value instanceof Collection) {
                        foreach($value as $collectionValue) {
                            $array[$key][] = $this->toArray($collectionValue, $fieldValue, $flatSingleKeys);
                        }
                    } else if (is_callable(array($value, 'toArray'))) {
                        $array[$key] = $value->toArray($fieldValue, $flatSingleKeys);
                    } else if ($value instanceof DateTime) {
                        $array[$key] = $value->format('Y-m-d H:i:s');
                    } else {
                        $array[$key] = $value;
                    }
                } else if (is_array($value) && count($value) > 0) {
                    $array[$key] = $this->toArray($value, $fieldValue, $flatSingleKeys, $value);
                } else if ($value !== NULL && !is_array($value)) {
                    $array[$key] = $value;
                }
            }
        }
        if ($flatSingleKeys) {
            foreach($fields as $field => $value) {
                if (!(isset($array[$field]) && is_array($value))) {
                    continue;
                }
                $array[$field] = $this->flatSingleKeys($array[$field]);
            }
        }
        return $array;
    }

    /**
     * Get entity manager
     *
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Validates the entity name
     *
     * @param string $entityName
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    protected function validateEntityName($entityName)
    {
        if (!class_exists($entityName)) {
            throw new Exception\InvalidArgumentException('Class ' .$entityName . ' not found');
        }
        $this->getObjectManager()->getMetadataFactory()->getAllMetadata();
        if (!$this->getObjectManager()->getMetadataFactory()->hasMetadataFor($entityName)) {
            throw new Exception\InvalidArgumentException('Class ' .$entityName . ' is not a valid entity');
        }
    }

    /**
     * Update a field
     *
     * @param object $entity
     * @param ClassMetadata $meta
     * @param string $field
     * @param mixed $value
     * @return void
     */
    protected function updateField($entity, ClassMetadata $meta, $field, $value)
    {
        $fieldMapping = $meta->getFieldMapping($field);
        $type = $fieldMapping['type'];
        switch ($type) {
            case 'datetime':
                $value = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                break;
            case 'time':
                $value = DateTime::createFromFormat('H:i:s', $value);
                break;
            case 'date':
                $value = DateTime::createFromFormat('Y-m-d', $value);
                break;
            // todo: other mapping types
        }
        $setter = $this->fieldToSetterMethod($field);
        if (method_exists($entity, $setter)) { // use setter
            $entity->{$setter}($value);
        } else { // use reflection
            $reflectionProperty = $meta->getReflectionProperty($field);
            $oldValue = $this->getValue($entity, $meta, $field);
            $this->updateProperty($entity, $reflectionProperty, $oldValue, $value, $field);
        }
    }

    /**
     * Update an association
     *
     * @param object $entity
     * @param ClassMetadata $meta
     * @param string $field
     * @param mixed $value
     * @param bool $clone
     * @return void
     */
    protected function updateAssociation($entity, ClassMetadata $meta, $field, $value, $clone = false)
    {
        $associationMapping = $meta->getAssociationMapping($field);
        $targetEntityName = $associationMapping['targetEntity'];

        if (isset($associationMapping['joinColumns'])) { // x-to-one mapping
            if (is_array($value)) {
                $targetEntity = $this->getEntityFromArray($targetEntityName, $value, $clone);
            } else {
                $identifier = array_shift($this->getObjectManager()->getClassMetadata($targetEntityName)->getIdentifier());
                $targetEntity = $this->loadEntity($targetEntityName, array($identifier => $value), $clone);
            }
            $reflectionProperty = $meta->getReflectionProperty($field);
            $oldValue = $reflectionProperty->getValue($entity);
            if (method_exists($targetEntity, '__load')) {
                $targetEntity->__load();
            }
            $this->updateProperty($entity, $reflectionProperty, $oldValue, $targetEntity, $field);
        } else if(is_array($value)) { // x-to-many mapping
            //value has to be an array
            $reflectionProperty = $meta->getReflectionProperty($field);
            $reflectionProperty->setAccessible(true);
            $collection = $reflectionProperty->getValue($entity);
            /* @var $collection Collection */
            foreach ($value as $data) {
                $targetMeta = $this->getObjectManager()->getClassMetadata($targetEntityName);
                $identifier = array_shift($targetMeta->getIdentifier());
                if (is_scalar($data)) {
                    $targetEntity = $this->loadEntity(
                        $targetEntityName,
                        array(
                            $identifier => $data
                        ),
                        $clone
                    );
                } else {
                    $targetEntity = $this->getEntityFromArray($targetEntityName, $data, $clone);
                }
                if (!$collection->contains($targetEntity)) {
                    $collection->add($targetEntity);
                }
            }
        }
    }

    /**
     * Load an entity
     *
     * @param string $entityName
     * @param array $data
     * @param bool $clone
     * @return object
     * @throws Exception\InvalidArgumentException
     */
    protected function loadEntity($entityName, array $data, $clone = false)
    {
        $this->validateEntityName($entityName);
        $identifier = array_shift($this->getObjectManager()->getClassMetadata($entityName)->getIdentifier());
        if (isset($data[$identifier])) {
            $id = $data[$identifier];
            $entity = $this->getObjectManager()->find($entityName, $id);
            if (!$entity) {
                throw new Exception\InvalidArgumentException('Invalid identifier given, id: ' . $id . ' , entity: ' . $entityName);
            } else if (false !== $clone) {
                if (!method_exists($entity, '__clone') && !is_callable(array($entity, '__clone'))) {
                    throw new Exception\InvalidArgumentException('Entity ' . $entityName . ' is not cloneable');
                }
                $entity = clone $entity;
            }
        } else {
            $entity = new $entityName;
        }
        return $entity;
    }

    /**
     * Get value from object
     *
     * @param object $entity
     * @param \Doctrine\ORM\Mapping\ClassMetadata $meta
     * @param string $field
     * @return mixed
     */
    protected function getValue($entity, ClassMetadata $meta, $field)
    {
        $reflectionProperty = $meta->getReflectionProperty($field);
        $value = $reflectionProperty->getValue($entity);
        return $value;
    }

    /**
     * Update reflection property
     *
     * @param object $entity
     * @param ReflectionProperty $reflectionProperty
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param string $field
     */
    protected function updateProperty($entity, ReflectionProperty $reflectionProperty, $oldValue, $newValue, $field)
    {
        if ($oldValue !== $newValue) {
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($entity, $newValue);
            if ($entity instanceof NotifyPropertyChanged && method_exists($this->getObjectManager(), 'getUnitOfWork')) {
                $this->getObjectManager()->getUnitOfWork()->propertyChanged($entity, $field, $oldValue, $newValue);
            }
        }
    }

    /**
     * Flat single keys
     *
     * @param array|null $data
     * @return array
     */
    protected function flatSingleKeys($data)
    {
        $result = array();
        if ($data === NULL) {
            return $data;
        }
        if (ArrayUtils::isHashTable($data)) {
            if (1 === count($data)) {
                $result = array_shift($data);
            } else {
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $result[$key] = $this->flatSingleKeys($value);
                    } else {
                        $result[$key] = $value;
                    }
                }
            }
        } else {
            foreach ($data as $value) {
                if (is_array($value) && 1 === count($value)) {
                    $result[] = array_shift($value);
                } elseif (is_scalar($value)) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Convert to camel case
     *
     * @param $name
     * @return string
     */
    protected function toCamelCase($name)
    {
        return lcfirst(implode('',array_map('ucfirst', explode('_',$name))));
    }

    /**
     * Convert field to setter method
     *
     * @param $name
     * @return string
     */
    protected function fieldToSetterMethod($name)
    {
        return 'set' . implode('',array_map('ucfirst', explode('_',$name)));
    }

    /**
     * Convert field to getter method
     *
     * @param $name
     * @return string
     */
    protected function fieldToGetterMethod($name)
    {
        return 'get' . implode('',array_map('ucfirst', explode('_',$name)));
    }

    /**
     * Convert from camel case
     *
     * @param string $name
     * @return string
     */
    protected function fromCamelCase($name)
    {
        return trim(preg_replace_callback('/([A-Z])/', function($c){ return '_'.strtolower($c[1]); }, $name),'_');
    }
}
