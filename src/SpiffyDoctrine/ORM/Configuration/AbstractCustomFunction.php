<?php
namespace SpiffyDoctrine\ORM\Configuration;

/**
 * A simple lightweight ValueObject representing a custom DQL function
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
abstract class AbstractCustomFunction {
    
    /**
     * Name of the custom function
     * 
     * @var type 
     */
    protected $name;
    
    /**
     * Classname of the custom function
     * 
     * @var string
     */
    protected $className;
    
    /**
     *
     * @param string $name
     * @param string $className 
     */
    public function __construct($name, $className)
    {
        $this->setName($name);
        $this->setClassName($className);
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
     * @param string $className 
     */
    public function setClassName($className)
    {
        $this->className = (string) $className;
    }
    
    /**
     * 
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
    
}