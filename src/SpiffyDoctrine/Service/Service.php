<?php
namespace SpiffyDoctrine\Service;
use Doctrine\DBAL\Types\Type,
    SpiffyDoctrine\Container\Container;

class Service 
{
    /**
     * @var SpiffyDoctrine\Container\Container
     */
    protected $_container;
    
    /**
     * Constructor.
     * 
     * @param SpiffyDoctrine\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->_container = $container;
    }
    
    /**
     * Get the Container.
     *  
     * @return Container
     */
    public function getContainer()
    {
        return $this->_container;
    }
    
    /**
     * Wrapper for Container::getEntityManager();
     * 
     * @return EntityManager
     */
    public function getEntityManager($emName = Container::DEFAULT_KEY)
    {
        return $this->getContainer()->getEntityManager($emName);
    }
    
   /**
     * Render this entity as an array.
     * 
    *  @param object $entity    The entity to convert to an array.
    *  @param string $emName    EntityManager name to use.
    * 
     * @return array
     */
    public function toArray($entity, $emName = Container::DEFAULT_KEY)
    {
        $em = $this->getEntityManager($emName);
        
        $result = array();
        foreach($em->getClassMetadata(get_class($entity))->getFieldNames() as $field) {
            $result[$field] = $this->_getFieldValue($entity, $field, $emName);
        }
        
        return $result;
    }
    
    /**
     * Gets a field mapping from metadata information.
     * 
     * @param object $entity
     * @param string $field
     * @param string $emName
     * 
     * @return null|array
     */
    public function getFieldMapping($entity, $field, $emName = Container::DEFAULT_KEY)
    {
        $mdata = $this->getEntityManager($emName)->getClassMetadata(get_class($entity));
        
        if (isset($mdata->fieldMappings[$field])) {
            return $mdata->getFieldMapping($field);
        }
        
        if (isset($mdata->associationMappings[$field])) {
            return $mdata->getAssociationMapping($field);
        }
        
        return null;
    }
    
    /**
     * Get an entity field value.
     * 
     * @param object $entity    The entity to retrieve the field from.
     * @param string $field     Field to retrieve value for.
     * @param string $emName    EntityManager name to use.
     * 
     * @return mixed
     */
    private function _getFieldValue($entity, $field, $emName = Container::DEFAULT_KEy)
    {
        $getter = 'get' . ucfirst($field);
        if (method_exists($entity, $getter)) {
            return $this->$getter();
        } else if (
            ($mapping = $this->getFieldMapping($entity, $field, $emName)) && 
            ($mapping['type'] == Type::BOOLEAN)
        ) {
            $isser = 'is' . ucfirst($field);
            if (method_exists(entity, $isser)) {
                return $this->$isser();
            }
        }
        
        $em = $this->getEntityManager($emName);
        return $em->getClassMetadata(get_class($entity))->reflFields[$field]->getValue($entity);
    }
}
