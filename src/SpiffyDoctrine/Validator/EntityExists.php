<?php
namespace SpiffyDoctrine\Validator;
use Doctrine\ORM\Query,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\NonUniqueResultException;

class EntityExists extends AbstractEntity
{
    public function isValid($value)
    {
        $query = $this->_getQuery($value);
        
        try {
            $result = $query->getSingleResult(Query::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return false;
        } catch (NonUniqueResultException $e) {
            return true;
        }
        
        return true;
    }
}