<?php
namespace SpiffyDoctrine\ORM\Configuration;

/**
 * A simple lightweight ValueObject representing a named query
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class NamedQuery {
    
    /**
     * Name of the named query
     * 
     * @var type 
     */
    protected $name;
    
    /**
     * DQL of the named query
     * 
     * @var string
     */
    protected $dql;
    
    /**
     *
     * @param string $modeName
     * @param string $dql 
     */
    public function __construct($name, $dql)
    {
        $this->setName($name);
        $this->setDql($dql);
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
     * @param string $dql 
     */
    public function setDql($dql)
    {
        $this->dql = (string) $dql;
    }
    
    /**
     * 
     * @return string
     */
    public function getDql()
    {
        return $this->dql;
    }
    
}