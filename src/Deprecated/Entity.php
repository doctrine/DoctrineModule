<?php
namespace SpiffyDoctrineEntity\Entity;
use Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\DBAL\Types\Type,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\EntityManager;

/**
 * AbstractEntity to provide additional functionality to any Doctrine entity
 * extending this class. Additional functionality includes:
 *      - toArray() and fromArray() methods
 *      - validation and filtering integrated with the entity
 */
abstract class Entity 
{
    /**
     * Determines if an entity is valid. Uses Zend\Validator\ValidatorChain
     * and optionally filters values before validation. Entity must implement
     * SpiffyDoctrineEntity\Entity\Validatable in order to be validatable. 
     * 
     * Available options:
     *   - filter: if false, disables filtering before validation (default: true).
     */
    public function isValid()
    {
        if (!$this instanceof Validatable) {
            throw new \BadMethodCallException(
                'Entity must implement the Validatable interface to be checked for validity.'
            );
        }
        
        $validatorChain = Factory::getValidatorChain($this);
        
    }
    
    /**
     * Render this entity as an array.
     * 
     * Available options:
     *   - empty: if false, disables output of empty values (default: true).
     *   - filter: if false, disables filtering (default: true).
     *   - assocations: an array of associations to convert to an array.
     * 
     * @param  array $options   Parameters to set how the array is returned.
     * 
     * @return array
     */
    public function toArray(array $options = array())
    {
        $result = array();
        
        $filterChain = null;
        if (!isset($options['filter']) || $options['filter'] !== false) {
            $filterChain = Factory::getFilterChain($this);
        }
        
        foreach(Factory::getMetadata(get_called_class())->getFieldNames() as $field) {
            $value = $this->_getFieldValue($field);

            if ($filterChain) {
                $value = $filterChain->filter($value);
            }
            
            $result[$field] = $value;
        }
        
        return $result;
    }
    
    /**
     * Set entity defaults from an array.
     * 
     * @param array $defaults   The default to set.
     * 
     * @return void
     */
    public function fromArray(array $defaults = array())
    {
        foreach($defaults as $key => $value) {
            // non-mapped fields
            if (!($map = Factory::getFieldMapping(get_called_class(), $key))) {
                $this->_setFieldValue($key, $value);
                continue;
            }

            // association mappings
            if ($map['type'] & (ClassMetadata::TO_MANY | ClassMetadata::TO_ONE)) {
                continue;
            }
            
            // standard entity fields
            $this->_setFieldValue($key, $value);
        }
    }
    
    /**
     * Set an entity field value.
     * 
     * @return void
     */
    private function _setFieldValue($field, $value)
    {
        $mdata = Factory::getMetadata(get_called_class());
        
        $setter = 'set' . ucfirst($field);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else if (isset($mdata->reflFields[$field])) {
            $mdata->reflFields[$field]->setValue($this, $value);
        }
    }
    
    /**
     * Get an entity field value.
     * 
     * @return mixed
     */
    private function _getFieldValue($field)
    {
        $getter = 'get' . ucfirst($field);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else if(
            ($mapping = Factory::getFieldMapping(get_called_class(), $field)) && 
            $mapping['type'] == Type::BOOLEAN
        ) {
            $isser = 'is' . ucfirst($field);
            if (method_exists($this, $isser)) {
                return $this->$isser();
            }
        }
        
        return Factory::getMetadata(get_called_class())->reflFields[$field]->getValue($this);
    }
}
