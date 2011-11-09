<?php
namespace SpiffyDoctrine\Validator;
use Doctrine\ORM\Query,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\NonUniqueResultException;

class NoEntityExists extends AbstractEntity
{
    public function isValid($value)
    {
        $query = $this->_getQuery($value);
        
        try {
            $result = $query->getSingleResult(Query::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return true;
        } catch (NonUniqueResultException $e) {
        	$this->error(self::ERROR_RECORD_FOUND, $value);
            return false;
        }
        
        $this->error(self::ERROR_RECORD_FOUND, $value);
        return false;
    }
}