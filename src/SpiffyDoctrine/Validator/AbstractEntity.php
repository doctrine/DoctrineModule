<?php
namespace SpiffyDoctrine\Validator;

use Closure,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\EntityRepository,
    Zend\Validator\AbstractValidator,
    Zend\Validator\Exception;

abstract class AbstractEntity extends AbstractValidator
{
    /**
     * Error constants
     */
    const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::ERROR_NO_RECORD_FOUND => "No record matching '%value%' was found",
        self::ERROR_RECORD_FOUND    => "A record matching '%value%' was found",
    );
    
    protected $_em;
    
    protected $_entity;
    
    protected $_field;
    
    protected $_query;
    
    protected $_queryBuilder;

    public function __construct(array $options)
    {
        if (!array_key_exists('em', $options)) {
            throw new Exception\InvalidArgumentException('No EntityManager was specified.');
        }
        
        if (!$options['em'] instanceof EntityManager) {
            throw new Exception\InvalidArgumentException('Invalid EntityManager specified.');
        }
        $this->_em = $options['em'];
        
        if (!array_key_exists('entity', $options)) {
            throw new Exception\InvalidArgumentException('No entity class was specified.');
        }
        $this->_entity = $options['entity'];
        
        if (array_key_exists('query', $options)) {
            $this->_query = $options['query'];
        } else if (array_key_exists('query_builder', $options)) {
            if (!$options['query_builder'] instanceof Closure) {
                throw new Exception\InvalidArgumentException('query_builder must be a Closure');
            }
            $this->_queryBuilder = $options['query_builder'];
        } 
        
        if (!$this->_query && !array_key_exists('field', $options)) {
            throw new Exception\InvalidArgumentException('You must specify a field if not using query.');
        }
        $this->_field = $options['field'];
    }

    protected function _getQuery($value)
    {
        if ($this->_query) {
            return $this->_query;
        }
        
        $em = $this->_em;
        $qb = $this->_queryBuilder;
        $field = $this->_field;
        
        // generate default query builder closure if one wasn't specified
        if (!$qb instanceof Closure) {
            $qb = function(EntityRepository $er) use ($field) {
                return $er->createQueryBuilder('q');
            };
        }

        $er = $em->getRepository($this->_entity);
        $qb = call_user_func($qb, $er);
        
        // reduce query to minimal return (selecting identifiers)
        $mdata = $em->getClassMetadata($this->_entity);
        $alias = current($qb->getDqlPart('from'))->getAlias();
        
        $select = "partial {$alias}.{";
        $select.= implode(',', $mdata->identifier);
        $select.= '}';
        
        $qb->select($select);
        
        // add field to query
        $qb->andWhere($qb->expr()->eq('q.' . $field, $qb->expr()->literal($value)))
           ->setMaxResults(1);
           
        return $qb->getQuery();
    }
}
