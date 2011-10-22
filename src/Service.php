<?php
namespace SpiffyDoctrine;
use Doctrine\ORM\EntityManager;

class Service 
{
    /**
     * @var EntityManager
     */
    protected $_em;
    
    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
    }
    
    /**
     * Get the EntityManager.
     *  
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }
}
