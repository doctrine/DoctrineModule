<?php
namespace SpiffyDoctrineEntity\Entity;
use Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Zend\Filter\FilterChain,
    Zend\Validator\ValidatorChain;

class Factory
{
    const FILTER_NAMESPACE = 'SpiffyDoctrineEntity\Annotation\Filter\Zend';
    const VALIDATOR_NAMESPACE = 'SpiffyDoctrineEntity\Annotation\Validator\Zend';
    
    /**
     * @var array
     */
    protected static $_metadata = array();
    
    /**
     * @var array
     */
    protected static $_annotations = array();
    
    /**
     * @var array
     */
    protected static $_filters = array();
    
    /**
     * @var array
     */
    protected static $_validators = array();
    
    /**
     * @var Doctrine\Common\Annotations\AnnotationReader
     */
    protected static $_reader;
    
    /**
     * Creates an entity.
     * 
     * @param Entity $entity         Instance of the entity.
     * @param EntityManager $em      EntityManager instance to use for constructing metadata.
     * @param array $defaults        Optional array of default values to pass to the entity.
     * 
     * @return Entity
     */
    public static function create(Entity $entity, EntityManager $em, array $defaults = array())
    {
        self::setMetadata(get_class($entity), $em->getClassMetadata(get_class($entity)));
        $entity->fromArray($defaults);
        
        return $entity;
    }
    
    /**
     * Gets the annotation reader and lazy-loads if necessary.
     * 
     * @return AnnotationReader
     */
    public static function getAnnotationReader()
    {
        if (null === self::$_reader) {
            self::$_reader = new AnnotationReader;
        }
        return self::$_reader;
    }
    
    /**
     * Gets the filter chain for an entity if it implements filterable.
     * 
     * @return null|Zend\Filter\FilterChain
     */
    public static function getFilterChain(Entity $entity)
    {
        if (!$entity instanceof Filterable) {
            return null;
        }
        
        if (!isset(self::$_filters[get_class($entity)])) {
            self::setFilterChain($entity);
        }
        return self::$_filters[get_class($entity)];
    }
    
    /**
     * Lazy-loads filters as a chain for the given entity.
     * 
     * @param Entity $entity
     */
    public static function setFilterChain(Entity $entity)
    {
        if (isset(self::$_filters[get_class($entity)])) {
            return;
        }
        
        $annotations = self::getAnnotations(
            get_class($entity),
            self::FILTER_NAMESPACE
        );
        
        $chain = new FilterChain;
        foreach($annotations as $field => $fieldAnnotations) {
            foreach($fieldAnnotations as $fa) {
                $chain->attach(new $fa->class($fa->value));
            }
        }
        
        self::$_filters[get_class($entity)] = $chain;
    }
    
    /**
     * Gets the validator chain for an entity if it implements validatable.
     * 
     * @return null|Zend\Validator\ValidatorChain
     */
    public static function getValidatorChain(Entity $entity)
    {
        if (!$entity instanceof Validatable) {
            return null;
        }
        
        if (!isset(self::$_validators[get_class($entity)])) {
            self::setValidatorChain($entity);
        }
        return self::$_validators[get_class($entity)];
    }
    
    /**
     * Lazy-loads validators as a chain for the given entity if the entity
     * implements validatable.
     * 
     * @param Entity $entity
     */
    public static function setValidatorChain(Entity $entity)
    {
        if (isset(self::$_validators[get_class($entity)])) {
            return;
        }
        
        
        $annotations = self::getAnnotations(
            get_class($entity),
            self::VALIDATOR_NAMESPACE
        );
        
        $chain = new ValidatorChain;
        foreach($annotations as $field => $fieldAnnotations) {
            foreach($fieldAnnotations as $fa) {
                $chain->addValidator(new $fa->class($fa->value));
            }
        }
        
        self::$_validators[get_class($entity)] = $chain;
    }
    
    /**
     * Gets the annotations for an entity as an array of field => annotation pairs.
     * Only processes each entity once and lazy-loads when necessary.
     * 
     * @param string $entityName    Name of the entity to retrieve annotations for.
     * @param string $namespace     If namespace is specified, only instances of that namespace 
     *                              are returned.
     * 
     * @return array
     */
    public static function getAnnotations($entityName, $namespace = null)
    {
        $reader = self::getAnnotationReader();
        
        if (!isset(self::$_annotations[$entityName])) {
            $mdata = self::getMetadata($entityName);
            foreach($mdata->getReflectionProperties() as $property) {
                self::$_annotations[$entityName][$property->name] = $reader->getPropertyAnnotations($property);
            }
        }
        
        if (null !== $namespace) {
            $filtered = array();
            foreach(self::$_annotations[$entityName] as $field => $annotations) {
                foreach($annotations as $annotation) {
                    if ($annotation instanceof $namespace) {
                        $filtered[$field][] = $annotation;
                    }
                }
            }
            
            return $filtered;
        }
        
        return self::$_annotations[$entityName];
    }
        
    /**
     * Gets a field mapping from metadata information.
     * 
     * @param string $entityName
     * @param string $field
     * 
     * @return null|array
     */
    public static function getFieldMapping($entityName, $field)
    {
        $mdata = self::getMetadata($entityName);
        if (isset($mdata->fieldMappings[$field])) {
            return $mdata->getFieldMapping($field);
        }
        
        if (isset($mdata->associationMappings[$field])) {
            return $mdata->getAssociationMapping($field);
        }
        
        return null;
    }
    
    /**
     * Gets the entity metadata.
     * 
     * @param string            $entityName
     * 
     * @return ClassMetadata
     */
    public static function getMetadata($entityName)
    {
        if (!isset(self::$_metadata[$entityName])) {
            throw new \BadMethodCallException('metadata information has not been set');
        }
        return self::$_metadata[$entityName];
    }
    
    /**
     * Sets the entity metadata.
     * 
     * @param string        $entityName
     * @param ClassMetadata $metadata
     */
    public static function setMetadata($entityName, ClassMetadata $metadata)
    {
        if (isset(self::$_metadata[$entityName])) {
            return;
        }
        self::$_metadata[$entityName] = $metadata;
    }
}
