<?php
namespace SpiffyDoctrine\ORM\Configuration;

use Doctrine\ORM\Query\ResultSetMapping;

/**
 * A simple lightweight ValueObject representing a named native query
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class NamedNativeQuery {
    
    /**
     * Name of the named native query
     * 
     * @var type 
     */
    protected $name;
    
    /**
     * SQL of the named native query
     * 
     * @var string
     */
    protected $sql;
    
    /**
     * ResultSetMapping of the named native query
     * 
     * @var ResultSetMapping
     */
    protected $rsm;
    
    /**
     *
     * @param string $modeName
     * @param string $sql 
     * @param ResultSetMapping $rsm
     */
    public function __construct($name, $sql, ResultSetMapping $rsm)
    {
        $this->setName($name);
        $this->setSql($sql);
        $this->setRsm($rsm);
    }
    
    /**
     * 
     * @param string $name 
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }
    
    /**
     * 
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * 
     * @param string $sql 
     */
    public function setSql($sql)
    {
        $this->sql = (string) $sql;
    }
    
    /**
     * 
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }
    
    /**
     * 
     * @param ResultSetMapping $rsm 
     */
    public function setRsm(ResultSetMapping $rsm)
    {
        $this->rsm = (string) $rsm;
    }
    
    /**
     * 
     * @return ResultSetMapping
     */
    public function getRsm()
    {
        return $this->rsm;
    }
    
}